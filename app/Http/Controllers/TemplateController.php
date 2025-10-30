<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Exports\LotTemplateExport;
use Maatwebsite\Excel\Facades\Excel;

class TemplateController extends Controller
{
    public function downloadLotTemplate()
    {
        return Excel::download(new LotTemplateExport, 'plantilla_importacion_lotes.xlsx');
    }
}