<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PromotionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'nom' => 'required|string|max:255|unique:promotions,nom',
            'description' => 'nullable|string|max:1000',
            'reduction' => 'required|numeric|min:1|max:100',
            'date_debut' => 'required|date|after_or_equal:today',
            'date_fin' => 'required|date|after:date_debut',
            'produits' => 'sometimes|array|min:1',
            'produits.*.produit_id' => 'required_with:produits|exists:produits,id',
            'produits.*.montant_reduction' => 'nullable|numeric|min:0'
        ];

        // Pour la mise à jour, on permet les modifications sans unicité stricte
        if ($this->isMethod('PUT') || $this->isMethod('PATCH')) {
            $promotionId = $this->route('promotion');
            $rules['nom'] = 'required|string|max:255|unique:promotions,nom,' . $promotionId;
            $rules['date_debut'] = 'required|date'; // Permet de modifier une promo existante
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'nom.required' => 'Le nom de la promotion est obligatoire',
            'nom.unique' => 'Une promotion avec ce nom existe déjà',
            'reduction.required' => 'Le pourcentage de réduction est obligatoire',
            'reduction.min' => 'La réduction doit être d\'au moins 1%',
            'reduction.max' => 'La réduction ne peut pas dépasser 100%',
            'date_debut.required' => 'La date de début est obligatoire',
            'date_debut.after_or_equal' => 'La date de début ne peut pas être antérieure à aujourd\'hui',
            'date_fin.required' => 'La date de fin est obligatoire',
            'date_fin.after' => 'La date de fin doit être postérieure à la date de début',
            'produits.*.produit_id.exists' => 'Le produit sélectionné n\'existe pas',
            'produits.min' => 'Au moins un produit doit être sélectionné'
        ];
    }

    protected function prepareForValidation()
    {
        // Nettoyer et préparer les données
        $this->merge([
            'nom' => trim($this->nom),
            'description' => $this->description ? trim($this->description) : null,
        ]);
    }
}
