<?php

namespace App\Http\Controllers;

use App\Models\Element;
use App\Models\Properties;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PropertiesController extends Controller
{
    public function getPropertyDatatype($slug, $seat_slug, $id, $element_slug)
    {
        try {
            $element = Element::where('slug', $element_slug)->first();
            if ($element) {
                $property = Properties::where('element_id', $element->id)->where('id', $id)->first();
                if ($property) {
                    return response()->json(['success' => true, 'property' => $property]);
                } else {
                    return response()->json(['success' => false, 'property' => 'Properties not found!']);
                }
            }
            return response()->json(['success' => false, 'properties' => 'Element not found!' . $element_slug]);
        } catch (Exception $e) {
            /* Log the exception message for debugging */
            Log::error($e);

            /* Redirect to the dashboard with a generic error message if an exception occurs */
            return redirect()->route('seatDashboardPage', ['slug' => $slug, 'seat_slug' => $seat_slug])
                ->withErrors(['error' => 'Something went wrong']);
        }
    }

    public function getPropertyRequired($slug, $seat_slug, $id)
    {
        try {
            $property = Properties::where('id', $id)->first();
            if ($property) {
                return response()->json(['success' => true, 'property' => $property]);
            } else {
                return response()->json(['success' => false, 'property' => 'Properties not found!']);
            }
        } catch (Exception $e) {
            /* Log the exception message for debugging */
            Log::error($e);

            /* Redirect to the dashboard with a generic error message if an exception occurs */
            return redirect()->route('seatDashboardPage', ['slug' => $slug, 'seat_slug' => $seat_slug])
                ->withErrors(['error' => 'Something went wrong']);
        }
    }
}
