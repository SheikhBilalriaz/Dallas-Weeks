<?php

namespace App\Http\Controllers;

use App\Models\Campaign;
use App\Models\Campaign_Element;
use App\Models\Lead_Action;
use App\Models\Seat;
use App\Models\Lead;
use App\Models\Team;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PDF;

class ReportController extends Controller
{
    function report($slug, $seat_slug)
    {
        try {
            $team = Team::where('slug', $slug)->first();
            $seat = Seat::where('slug', $seat_slug)->first();
            $campaignIds = Campaign::where('seat_id', $seat->id)->pluck('id')->toArray();
            $campaignElements = Campaign_Element::whereIn('campaign_id', $campaignIds)
                ->where(function ($query) {
                    $query->where('slug', 'like', 'view_profile%')
                        ->orWhere('slug', 'like', 'invite_to_connect%')
                        ->orWhere('slug', 'like', 'message%')
                        ->orWhere('slug', 'like', 'inmail_message%')
                        ->orWhere('slug', 'like', 'email_message%')
                        ->orWhere('slug', 'like', 'follow%');
                })->get()->groupBy(function ($element) {
                    if (is_string($element->slug)) {
                        if (str_starts_with($element->slug, 'view_profile')) return 'view_profile';
                        if (str_starts_with($element->slug, 'invite_to_connect')) return 'invite_to_connect';
                        if (str_starts_with($element->slug, 'email_message')) return 'email_message';
                        if (str_starts_with($element->slug, 'follow')) return 'follow';
                        if (str_starts_with($element->slug, 'message')) return 'message';
                        if (str_starts_with($element->slug, 'inmail_message')) return 'inmail_message';
                    }
                    return 'other';
                });
            $leadActions = Lead_Action::whereIn('campaign_id', $campaignIds)->get()
                ->groupBy(function ($item) {
                    return \Carbon\Carbon::parse($item->created_at)->format('Y-m-d');
                });
            $reports = [];
            foreach ($leadActions as $date => $actions) {
                $reports[$date] = [
                    'invite_count' => isset($campaignElements['invite_to_connect'])
                        ? $actions->whereIn('current_element_id', $campaignElements['invite_to_connect']->pluck('id'))->where('status', 'completed')->count()
                        : 0,
                    'email_count' => isset($campaignElements['email_message'])
                        ? $actions->whereIn('current_element_id', $campaignElements['email_message']->pluck('id'))->where('status', 'completed')->count()
                        : 0,
                    'view_count' => isset($campaignElements['view_profile'])
                        ? $actions->whereIn('current_element_id', $campaignElements['view_profile']->pluck('id'))->where('status', 'completed')->count()
                        : 0,
                    'follow_count' => isset($campaignElements['follow'])
                        ? $actions->whereIn('current_element_id', $campaignElements['follow']->pluck('id'))->where('status', 'completed')->count()
                        : 0,
                ];
            }
            $lead_actions_of_pre_month = Lead_Action::whereIn('campaign_id', $campaignIds)
                ->whereBetween(
                    'created_at',
                    [
                        Carbon::now()->subMonth()->startOfDay(),
                        Carbon::now()->endOfDay()
                    ]
                )->get()->groupBy(function ($item) {
                    return \Carbon\Carbon::parse($item->created_at)->format('Y-m-d');
                });
            $past_month_reports = [];
            for ($date = Carbon::now()->subMonth()->startOfDay(); $date <= Carbon::now()->endOfDay(); $date->addDay()) {
                $formattedDate = $date->format('Y-m-d');
                $past_month_reports[$formattedDate] = [
                    'invite_count' => isset($campaignElements['invite_to_connect'])
                        ? $lead_actions_of_pre_month->get($formattedDate, collect())->whereIn('current_element_id', $campaignElements['invite_to_connect']->pluck('id'))->where('status', 'completed')->count()
                        : 0,
                    'email_count' => isset($campaignElements['email_message'])
                        ? $lead_actions_of_pre_month->get($formattedDate, collect())->whereIn('current_element_id', $campaignElements['email_message']->pluck('id'))->where('status', 'completed')->count()
                        : 0,
                    'view_count' => isset($campaignElements['view_profile'])
                        ? $lead_actions_of_pre_month->get($formattedDate, collect())->whereIn('current_element_id', $campaignElements['view_profile']->pluck('id'))->where('status', 'completed')->count()
                        : 0,
                    'follow_count' => isset($campaignElements['follow'])
                        ? $lead_actions_of_pre_month->get($formattedDate, collect())->whereIn('current_element_id', $campaignElements['follow']->pluck('id'))->where('status', 'completed')->count()
                        : 0,
                    'message_count' => isset($campaignElements['message'])
                        ? $lead_actions_of_pre_month->get($formattedDate, collect())->whereIn('current_element_id', $campaignElements['message']->pluck('id'))->where('status', 'completed')->count()
                        : 0,
                    'in_mail_count' => isset($campaignElements['inmail_message'])
                        ? $lead_actions_of_pre_month->get($formattedDate, collect())->whereIn('current_element_id', $campaignElements['inmail_message']->pluck('id'))->where('status', 'completed')->count()
                        : 0,
                ];
            }
            $data = [
                'title' => 'Dashboard - Report',
                'team' => $team,
                'seat' => $seat,
                'reports' => $reports,
                'past_month_data' => $past_month_reports,
            ];
            return view('back.reports', $data);
        } catch (Exception $e) {
            /* Log the exception message for debugging */
            Log::error($e);

            /* Redirect to the dashboard with a generic error message if an exception occurs */
            return redirect()->route('seatDashboardPage', ['slug' => $slug, 'seat_slug' => $seat_slug])
                ->withErrors(['error' => 'Something went wrong']);
        }
    }
    
    public function generate_report($slug, $seat_slug)
    {
        $seat = Seat::where('slug', $seat_slug)->firstOrFail();
        $campaignIds = Campaign::where('seat_id', $seat->id)->pluck('id')->toArray();
        $campaignElements = Campaign_Element::whereIn('campaign_id', $campaignIds)
            ->where(function ($query) {
                $query->where('slug', 'like', 'view_profile%')
                    ->orWhere('slug', 'like', 'invite_to_connect%')
                    ->orWhere('slug', 'like', 'message%')
                    ->orWhere('slug', 'like', 'inmail_message%')
                    ->orWhere('slug', 'like', 'email_message%')
                    ->orWhere('slug', 'like', 'follow%');
            })->get()->groupBy(function ($element) {
                if (is_string($element->slug)) {
                    if (str_starts_with($element->slug, 'view_profile')) return 'view_profile';
                    if (str_starts_with($element->slug, 'invite_to_connect')) return 'invite_to_connect';
                    if (str_starts_with($element->slug, 'email_message')) return 'email_message';
                    if (str_starts_with($element->slug, 'follow')) return 'follow';
                    if (str_starts_with($element->slug, 'message')) return 'message';
                    if (str_starts_with($element->slug, 'inmail_message')) return 'inmail_message';
                }
                return 'other';
            });
        $startDate = Carbon::parse($seat->created_at)->startOfMonth();
        $endDate = Carbon::now()->startOfMonth();
        $spreadsheet = new Spreadsheet();
        $writer = new Xlsx($spreadsheet);
        $writer->setIncludeCharts(true);
        while ($startDate <= $endDate) {
            $monthStart = $startDate->copy()->startOfMonth();
            $monthEnd = $startDate->copy()->endOfMonth();
            $sheetName = $startDate->format('M-Y');
            $sheet = $spreadsheet->createSheet();
            $sheet->setTitle($sheetName);
            $sheet->setCellValue('A1', 'Date');
            $sheet->setCellValue('B1', 'Invites');
            $sheet->setCellValue('C1', 'Emails');
            $sheet->setCellValue('D1', 'Profile Views');
            $sheet->setCellValue('E1', 'Follows');
            $sheet->setCellValue('F1', 'Messages');
            $sheet->setCellValue('G1', 'In Mails');
            $lead_actions_of_month = Lead_Action::whereIn('campaign_id', $campaignIds)
                ->whereBetween('created_at', [$monthStart, $monthEnd])
                ->get()
                ->groupBy(function ($item) {
                    return Carbon::parse($item->created_at)->format('Y-m-d');
                });
            $row = 2;
            for ($date = $monthStart->copy(); $date <= $monthEnd; $date->addDay()) {
                $formattedDate = $date->format('Y-m-d');
                $dailyData = [
                    'invite_count' => isset($campaignElements['invite_to_connect'])
                        ? $lead_actions_of_month->get($formattedDate, collect())->whereIn('current_element_id', $campaignElements['invite_to_connect']->pluck('id'))->where('status', 'completed')->count()
                        : 0,
                    'email_count' => isset($campaignElements['email_message'])
                        ? $lead_actions_of_month->get($formattedDate, collect())->whereIn('current_element_id', $campaignElements['email_message']->pluck('id'))->where('status', 'completed')->count()
                        : 0,
                    'view_count' => isset($campaignElements['view_profile'])
                        ? $lead_actions_of_month->get($formattedDate, collect())->whereIn('current_element_id', $campaignElements['view_profile']->pluck('id'))->where('status', 'completed')->count()
                        : 0,
                    'follow_count' => isset($campaignElements['follow'])
                        ? $lead_actions_of_month->get($formattedDate, collect())->whereIn('current_element_id', $campaignElements['follow']->pluck('id'))->where('status', 'completed')->count()
                        : 0,
                    'message_count' => isset($campaignElements['message'])
                        ? $lead_actions_of_month->get($formattedDate, collect())->whereIn('current_element_id', $campaignElements['message']->pluck('id'))->where('status', 'completed')->count()
                        : 0,
                    'in_mail_count' => isset($campaignElements['inmail_message'])
                        ? $lead_actions_of_month->get($formattedDate, collect())->whereIn('current_element_id', $campaignElements['inmail_message']->pluck('id'))->where('status', 'completed')->count()
                        : 0,
                ];
                $sheet->setCellValue("A$row", $formattedDate);
                $sheet->setCellValue("B$row", $dailyData['invite_count']);
                $sheet->setCellValue("C$row", $dailyData['email_count']);
                $sheet->setCellValue("D$row", $dailyData['view_count']);
                $sheet->setCellValue("E$row", $dailyData['follow_count']);
                $sheet->setCellValue("F$row", $dailyData['message_count']);
                $sheet->setCellValue("G$row", $dailyData['in_mail_count']);
                $row++;
            }
            $startDate->addMonth();
        }
        $spreadsheet->removeSheetByIndex(0);
        $fileName = public_path("report_$seat_slug.xlsx");
        $writer->save($fileName);
        return response()->download($fileName)->deleteFileAfterSend(true);
    }
    
    public function generate_pdf($slug, $seat_slug)
    {
        $seat = Seat::where('slug', $seat_slug)->firstOrFail();
        $campaignIds = Campaign::where('seat_id', $seat->id)->pluck('id')->toArray();
        $campaignElements = Campaign_Element::whereIn('campaign_id', $campaignIds)
            ->where(function ($query) {
                $query->where('slug', 'like', 'view_profile%')
                    ->orWhere('slug', 'like', 'invite_to_connect%')
                    ->orWhere('slug', 'like', 'message%')
                    ->orWhere('slug', 'like', 'inmail_message%')
                    ->orWhere('slug', 'like', 'email_message%')
                    ->orWhere('slug', 'like', 'follow%');
            })->get()->groupBy(function ($element) {
                if (is_string($element->slug)) {
                    if (str_starts_with($element->slug, 'view_profile')) return 'view_profile';
                    if (str_starts_with($element->slug, 'invite_to_connect')) return 'invite_to_connect';
                    if (str_starts_with($element->slug, 'email_message')) return 'email_message';
                    if (str_starts_with($element->slug, 'follow')) return 'follow';
                    if (str_starts_with($element->slug, 'message')) return 'message';
                    if (str_starts_with($element->slug, 'inmail_message')) return 'inmail_message';
                }
                return 'other';
            });
        $startDate = Carbon::parse($seat->created_at)->startOfMonth();
        $endDate = Carbon::now()->startOfMonth();
        $reportData = [];
        while ($startDate <= $endDate) {
            $monthStart = $startDate->copy()->startOfMonth();
            $monthEnd = $startDate->copy()->endOfMonth();
            $lead_actions_of_month = Lead_Action::whereIn('campaign_id', $campaignIds)
                ->whereBetween('created_at', [$monthStart, $monthEnd])
                ->get()
                ->groupBy(function ($item) {
                    return Carbon::parse($item->created_at)->format('Y-m-d');
                });
            $monthlyData = [];
            for ($date = $monthStart->copy(); $date <= $monthEnd; $date->addDay()) {
                $formattedDate = $date->format('Y-m-d');
                $dailyData = [
                    'invite_count' => isset($campaignElements['invite_to_connect'])
                        ? $lead_actions_of_month->get($formattedDate, collect())->whereIn('current_element_id', $campaignElements['invite_to_connect']->pluck('id'))->where('status', 'completed')->count()
                        : 0,
                    'email_count' => isset($campaignElements['email_message'])
                        ? $lead_actions_of_month->get($formattedDate, collect())->whereIn('current_element_id', $campaignElements['email_message']->pluck('id'))->where('status', 'completed')->count()
                        : 0,
                    'view_count' => isset($campaignElements['view_profile'])
                        ? $lead_actions_of_month->get($formattedDate, collect())->whereIn('current_element_id', $campaignElements['view_profile']->pluck('id'))->where('status', 'completed')->count()
                        : 0,
                    'follow_count' => isset($campaignElements['follow'])
                        ? $lead_actions_of_month->get($formattedDate, collect())->whereIn('current_element_id', $campaignElements['follow']->pluck('id'))->where('status', 'completed')->count()
                        : 0,
                    'message_count' => isset($campaignElements['message'])
                        ? $lead_actions_of_month->get($formattedDate, collect())->whereIn('current_element_id', $campaignElements['message']->pluck('id'))->where('status', 'completed')->count()
                        : 0,
                    'in_mail_count' => isset($campaignElements['inmail_message'])
                        ? $lead_actions_of_month->get($formattedDate, collect())->whereIn('current_element_id', $campaignElements['inmail_message']->pluck('id'))->where('status', 'completed')->count()
                        : 0,
                ];
                $monthlyData[] = array_merge(['date' => $formattedDate], $dailyData);
            }
            $reportData[] = [
                'month' => $startDate->format('M-Y'),
                'data' => $monthlyData,
            ];
            $startDate->addMonth();
        }
        $pdf = PDF::loadView('pdf.report', ['reportData' => $reportData]);
        return $pdf->download("report_$seat_slug.pdf");
    }
}
