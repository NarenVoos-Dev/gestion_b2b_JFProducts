<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class LegalController extends Controller
{
    /**
     * Mostrar términos y condiciones
     */
    public function showTerms()
    {
        return view('legal.terms');
    }

    /**
     * Mostrar política de privacidad
     */
    public function showPrivacy()
    {
        return view('legal.privacy');
    }

    /**
     * Mostrar política de tratamiento de datos
     */
    public function showDataTreatment()
    {
        return view('legal.data-treatment');
    }
}
