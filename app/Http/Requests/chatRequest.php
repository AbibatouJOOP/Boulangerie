<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class chatRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'client_id'   => 'required|integer|exists:users,id',
            'employe_id'  => 'nullable|integer|exists:users,id',
            'message'     => 'required|string|max:1000',
            'emeteur_type'=> 'required|string|in:CLIENT,EMPLOYE,ADMIN',
            'emeteur_id'  => 'required|integer|exists:users,id',
            'est_lu'      => 'nullable|boolean',
        ];
    }

    public function messages()
    {
        return [
            'client_id.required'      => 'L\'ID du client est requis.',
            'client_id.exists'        => 'Le client spécifié n\'existe pas.',
            'employe_id.exists'       => 'L\'employé spécifié n\'existe pas.',
            'message.required'        => 'Le message ne peut pas être vide.',
            'message.max'             => 'Le message ne peut pas dépasser 1000 caractères.',
            'emeteur_type.required'   => 'Le type de l\'émetteur est requis.',
            'emeteur_type.in'         => 'Le type de l\'émetteur doit être CLIENT, EMPLOYE ou ADMIN.',
            'emeteur_id.required'     => 'L\'ID de l\'émetteur est requis.',
            'emeteur_id.exists'       => 'L\'émetteur spécifié n\'existe pas.',
        ];
    }
}
