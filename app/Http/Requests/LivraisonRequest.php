<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LivraisonRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'commande_id' => 'required|exists:commandes,id',
            'employe_id' => 'nullable|exists:users,id',
            'adresse_livraison' => 'required|string|max:500',
            'date_livraison' => 'required|date|after:now',
            'statut' => 'required|in:non_livrée,livrée',
            'frais_livraison' => 'nullable|numeric|min:0|max:50000',
            'note' => 'nullable|string|max:1000'
        ];

        // Pour la mise à jour, les règles peuvent être moins strictes
        if ($this->isMethod('PUT') || $this->isMethod('PATCH')) {
            $rules['commande_id'] = 'sometimes|exists:commandes,id';
            $rules['adresse_livraison'] = 'sometimes|string|max:500';
            $rules['date_livraison'] = 'sometimes|date';
            $rules['statut'] = 'sometimes|in:non_livrée,livrée';
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'commande_id.required' => 'L\'ID de la commande est obligatoire',
            'commande_id.exists' => 'La commande spécifiée n\'existe pas',
            'employe_id.exists' => 'L\'employé spécifié n\'existe pas',
            'adresse_livraison.required' => 'L\'adresse de livraison est obligatoire',
            'adresse_livraison.max' => 'L\'adresse de livraison ne peut pas dépasser 500 caractères',
            'date_livraison.required' => 'La date de livraison est obligatoire',
            'date_livraison.after' => 'La date de livraison doit être dans le futur',
            'statut.required' => 'Le statut de livraison est obligatoire',
            'statut.in' => 'Le statut de livraison doit être: non_livrée, en_cours, livrée ou annulée',
            'frais_livraison.numeric' => 'Les frais de livraison doivent être un nombre',
            'frais_livraison.min' => 'Les frais de livraison ne peuvent pas être négatifs',
            'frais_livraison.max' => 'Les frais de livraison ne peuvent pas dépasser 50,000 FCFA',
            'note.max' => 'La note ne peut pas dépasser 1000 caractères'
        ];
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'adresse_livraison' => $this->adresse_livraison ? trim($this->adresse_livraison) : null,
            'note' => $this->note ? trim($this->note) : null
        ]);
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // Vérifier que l'employé assigné a bien le rôle EMPLOYE
            if ($this->employe_id) {
                $employe = \App\Models\User::find($this->employe_id);
                if ($employe && $employe->role !== 'EMPLOYE') {
                    $validator->errors()->add('employe_id', 'L\'utilisateur sélectionné n\'est pas un employé');
                }
            }

            // Vérifier que la commande n'a pas déjà une livraison (pour création uniquement)
            if ($this->isMethod('POST') && $this->commande_id) {
                $livraisonExistante = \App\Models\Livraisons::where('commande_id', $this->commande_id)->exists();
                if ($livraisonExistante) {
                    $validator->errors()->add('commande_id', 'Cette commande a déjà une livraison associée');
                }
            }
        });
    }
}
