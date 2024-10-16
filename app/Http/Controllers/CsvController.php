<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Validator;
use App\Models\Seat;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class CsvController extends Controller
{
    public function import_csv($slug, $seat_slug, Request $request)
    {
        try {
            /* Validate the file input to ensure it's a CSV or TXT file. */
            $validator = Validator::make($request->all(), [
                'campaign_url' => 'required|file|mimes:csv,txt'
            ]);

            /* Return validation errors, if any. */
            if ($validator->fails()) {
                return back()->withErrors($validator)
                    ->withInput();
            }

            /* Retrieve the current user ID. */
            $user_id = Auth::user()->id;

            /* Check for file upload errors. */
            $file = $request->file('campaign_url');
            if ($file->getError() !== UPLOAD_ERR_OK) {
                return response()->json(['success' => false, 'message' => "Error uploading file"]);
            }

            /* Validate the file type (only CSV is allowed). */
            $validMimeTypes = ['text/csv', 'application/vnd.ms-excel'];
            if (!in_array($file->getClientMimeType(), $validMimeTypes)) {
                return response()->json(['success' => false, 'message' => "Invalid file type. Please upload a CSV file."]);
            }

            /* Generate a unique file name and store it in the uploads directory. */
            $fileName = 'imported_leads_' . time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();
            $uploadFilePath = $file->storeAs('uploads/', $fileName);

            /* Ensure file storage was successful. */
            if (!$uploadFilePath) {
                return response()->json(['success' => false, 'message' => "Error storing file"]);
            }

            /* Open the CSV file and read the data. */
            $fileHandle = fopen(storage_path('app/' . $uploadFilePath), 'r');
            if ($fileHandle === false) {
                Storage::delete($uploadFilePath);
                return response()->json(['success' => false, 'message' => "Error reading file"]);
            }


            $seat = Seat::where('slug', $seat_slug);

            /* Initialize CSV processing variables. */
            $csvData = [];
            $columnNames = fgetcsv($fileHandle);
            $delimiter = ',';

            /* Map columns to empty arrays for further data collection. */
            foreach ($columnNames as $colName) {
                $csvData[$colName] = [];
            }

            /* Read through each row and map data to the respective columns. */
            while (($rowData = fgetcsv($fileHandle, 0, $delimiter)) !== false) {
                foreach ($columnNames as $index => $colName) {
                    $csvData[$colName][] = $rowData[$index] ?? null;
                }
            }

            /* Close the file after reading. */
            fclose($fileHandle);

            /* Process the URLs from the CSV data. */
            $rowCount = 0;
            $totalUrls = [];
            $hasUrlColumn = false;
            $duplicates = 0;
            $totalProcessed = 0;
            $duplicates_across_team = 0;
            $global_blacklists = 0;

            foreach ($csvData as $key => $values) {
                /* Normalize column names to detect profile URL fields. */
                $normalizedKey = strtolower(str_replace(['_', ' ', '-', ',', ';'], '', $key));

                if (str_contains($normalizedKey, 'profileurl')) {
                    $hasUrlColumn = true;

                    /* Process each URL. */
                    foreach ($values as $url) {
                        $rowCount++;

                        /* If the URL is already in the list, count it as a duplicate. */
                        if (in_array($url, $totalUrls)) {
                            $duplicates++;
                            continue;
                        }

                        $lc = new LeadsController();
                        if ($lc->duplicateURLCampaign($slug, $seat_slug, $url)) {
                            $duplicates_across_team++;
                            continue;
                        }

                        if ($lc->blacklistURLCampaign($slug, $seat_slug, $url)) {
                            $global_blacklists++;
                            continue;
                        }

                        /* Validate if it's a proper LinkedIn URL. */
                        if (filter_var($url, FILTER_VALIDATE_URL) && (stripos($url, 'linkedin.com/in/') !== false || stripos($url, 'linkedin.com/company/') !== false)) {
                            $totalUrls[] = $url;
                            $totalProcessed++;
                        } else {
                            /* If URL is invalid, delete the uploaded file and return an error. */
                            Storage::delete($uploadFilePath);
                            return response()->json(['success' => false, 'message' => "Invalid LinkedIn URL at row " . $rowCount, 'url' => $url]);
                        }
                    }
                }
            }

            if (!$hasUrlColumn) {
                fclose($fileHandle);
                Storage::delete($uploadFilePath);
                return response()->json(['success' => false, 'message' => 'No Profile Url Column Found']);
            }

            return response()->json([
                'success' => true,
                'duplicates' => $duplicates,
                'duplicates_across_team' => $duplicates_across_team,
                'global_blacklists' => $global_blacklists,
                'path' => $fileName,
                'total' => $rowCount,
                'total_without_duplicate_blacklist' => $totalProcessed
            ]);
        } catch (Exception $e) {
            Log::error($e);
            return response()->json(['success' => false, 'message' => 'Something went wrong'], 500);
        }
    }
}
