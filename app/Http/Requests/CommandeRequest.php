<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CommandeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'produits' => 'required|array|min:1',
            'produits.*.produit_id' => 'required|integer|exists:produits,id',
            'produits.*.quantite' => 'required|integer|min:1',
            'produits.*.prixU' => 'required|numeric|min:0',
            'produits.*.promo_id' => 'nullable|integer|exists:promotions,id',

            'montant_total' => 'required|numeric|min:0',

            'infos_livraison' => 'required|array',
            'infos_livraison.nomComplet' => 'required|string|max:255',
            'infos_livraison.telephone' => 'required|string|max:20|regex:/^(\+221)?[0-9\s\-\(\)]{8,}$/',
            'infos_livraison.adresse' => 'required|string|max:500',
            'infos_livraison.ville' => 'required|string|max:255',
            'infos_livraison.codePostal' => 'nullable|string|max:10',
            'infos_livraison.commentaires' => 'nullable|string|max:1000',
            'infos_livraison.fraisLivraison' => 'nullable|numeric|min:0|max:50000',

            'infos_paiement' => 'required|array',
            'infos_paiement.modePaiement' => 'required|in:livraison,enligne',
            'infos_paiement.numeroTelephone' => 'nullable|string|max:20|regex:/^(\+221)?[0-9\s\-\(\)]{8,}$/',
            'infos_paiement.operateur' => 'nullable|in:orange,mtn,moov',

            'statut' => 'sometimes|string|in:en_préparation,prete,en_livraison,livrée,annulée'
        ];

        return $rules;
    }

    public function messages(): array
    {
        return [
            'produits.required' => 'Au moins un produit doit être commandé',
            'produits.min' => 'Au moins un produit doit être commandé',
            'produits.*.produit_id.required' => 'L\'ID du produit est obligatoire',
            'produits.*.produit_id.exists' => 'Le produit spécifié n\'existe pas',
            'produits.*.quantite.required' => 'La quantité est obligatoire',
            'produits.*.quantite.min' => 'La quantité doit être d\'au moins 1',


            'montant_total.required' => 'Le montant total est obligatoire',
            'montant_total.min' => 'Le montant total ne peut pas être négatif',

            'infos_livraison.required' => 'Les informations de livraison sont obligatoires',
            'infos_livraison.nomComplet.required' => 'Le nom complet est obligatoire',
            'infos_livraison.telephone.required' => 'Le téléphone est obligatoire',
            'infos_livraison.telephone.regex' => 'Le format du téléphone n\'est pas valide',
            'infos_livraison.adresse.required' => 'L\'adresse est obligatoire',
            'infos_livraison.ville.required' => 'La ville est obligatoire',
            'infos_livraison.fraisLivraison.max' => 'Les frais de livraison ne peuvent pas dépasser 50,000 FCFA',

            'infos_paiement.required' => 'Les informations de paiement sont obligatoires',
            'infos_paiement.modePaiement.required' => 'Le mode de paiement est obligatoire',
            'infos_paiement.modePaiement.in' => 'Le mode de paiement doit être "livraison" ou "enligne"',
            'infos_paiement.numeroTelephone.regex' => 'Le format du numéro de téléphone n\'est pas valide',
            'infos_paiement.operateur.in' => 'L\'opérateur doit être orange, mtn ou moov'
        ];
    }

    protected function prepareForValidation()
    {
        // Nettoyer les données
        if ($this->has('infos_livraison')) {
            $infosLivraison = $this->infos_livraison;
            $infosLivraison['nomComplet'] = trim($infosLivraison['nomComplet'] ?? '');
            $infosLivraison['telephone'] = trim($infosLivraison['telephone'] ?? '');
            $infosLivraison['adresse'] = trim($infosLivraison['adresse'] ?? '');
            $infosLivraison['ville'] = trim($infosLivraison['ville'] ?? '');
            $this->merge(['infos_livraison' => $infosLivraison]);
        }

        if ($this->has('infos_paiement')) {
            $infosPaiement = $this->infos_paiement;
            if (isset($infosPaiement['numeroTelephone'])) {
                $infosPaiement['numeroTelephone'] = trim($infosPaiement['numeroTelephone']);
            }
            $this->merge(['infos_paiement' => $infosPaiement]);
        }
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // Vérifier la disponibilité du stock
            if ($this->has('produits')) {
                foreach ($this->produits as $index => $produitData) {
                    $produit = \App\Models\Produits::find($produitData['produit_id']);
                    if ($produit && $produit->stock < $produitData['quantite']) {
                        $validator->errors()->add(
                            "produits.{$index}.quantite",
                            "Stock insuffisant pour {$produit->nom}. Stock disponible: {$produit->stock}"
                        );
                    }
                }
            }

            // Si paiement en ligne, vérifier numero et operateur
            if ($this->has('infos_paiement') && $this->infos_paiement['modePaiement'] === 'enligne') {
                if (empty($this->infos_paiement['numeroTelephone'])) {
                    $validator->errors()->add('infos_paiement.numeroTelephone', 'Le numéro de téléphone est obligatoire pour le paiement en ligne');
                }
                if (empty($this->infos_paiement['operateur'])) {
                    $validator->errors()->add('infos_paiement.operateur', 'L\'opérateur est obligatoire pour le paiement en ligne');
                }
            }

            // Vérifier que les promotions sont toujours actives
            if ($this->has('produits')) {
                foreach ($this->produits as $index => $produitData) {
                    if (!empty($produitData['promo_id'])) {
                        $promotion = \App\Models\Promotion::find($produitData['promo_id']);
                        if (!$promotion ||
                            $promotion->dateDebut > now() ||
                            $promotion->dateFin < now()) {
                            $validator->errors()->add(
                                "produits.{$index}.promo_id",
                                "La promotion sélectionnée n'est plus active"
                            );
                        }
                    }
                }
            }
        });
    }
}
