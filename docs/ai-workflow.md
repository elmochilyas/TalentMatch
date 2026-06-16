# Pipeline d'Analyse IA — TalentMatch

Ce document décrit le flux complet de l'analyse IA, de la soumission du CV à l'affichage des résultats.

## Architecture du pipeline

```
Soumission formulaire
       │
       ▼
   Validation (Form Request)
       │
       ▼
   Dispatch Job AnalyseCV
       │
       ▼
   File d'attente (QUEUE_CONNECTION=database)
       │
       ▼
   Worker queue:work
       │
       ▼
   Appel fournisseur IA
       │
       ▼
   Parsing JSON structuré
       │
       ▼
   Stockage base de données
       │
       ▼
   Affichage dans l'interface
```

## Flux détaillé

### 1. Soumission du formulaire

L'agent RH soumet un formulaire avec :
- `nom_candidat` — nom du candidat
- `cv_texte` — texte brut du CV
- `offre_id` — l'offre cible (via la route)

**Validation** : Le `CandidatureRequest` (Form Request) valide que :
- `nom_candidat` est requis (chaîne, max 255 caractères)
- `cv_texte` est requis et non vide (longueur minimale)
- L'offre existe et appartient à l'utilisateur connecté

### 2. Dispatch du Job

Une fois la validation passée, un `AnalyseCV` Job est dispatché :

```php
AnalyseCV::dispatch($analyseCandidat);
```

Le Job reçoit l'ID de l'`AnalyseCandidat` et :
1. Définit `statut_analyse = processing`
2. Appelle l'IA via le SDK `laravel/ai`
3. Parse la réponse JSON
4. Stocke les résultats
5. Définit `statut_analyse = completed` (ou `failed` en cas d'erreur)

### 3. File d'attente

La configuration par défaut utilise `QUEUE_CONNECTION=database` (table `jobs`).

Pour traiter les jobs, le worker doit être actif :

```bash
php artisan queue:work
```

**Alternative pour les tests** : Configurer `QUEUE_CONNECTION=sync` dans `.env` pour exécuter les jobs de manière synchrone (utile en développement local).

### 4. Appel au fournisseur IA

Le Job appelle le fournisseur IA configuré (OpenAI par défaut) avec un prompt qui inclut :
- Le texte du CV soumis
- Les compétences requises et le niveau d'expérience de l'offre
- Les instructions pour générer une réponse JSON structurée

### 5. Sortie structurée JSON

L'IA doit retourner un JSON respectant ce schéma strict :

```json
{
  "competences_extraites": ["PHP", "Laravel", "MySQL"],
  "annees_experience": 5,
  "niveau_etudes": "Master en Informatique",
  "langues": ["Français", "Anglais"],
  "matching_score": 85,
  "points_forts": [
    "Solide expérience Laravel",
    "Maîtrise des API REST",
    "Expérience en équipe"
  ],
  "lacunes": [
    "Pas d'expérience en DevOps"
  ],
  "competences_manquantes": ["Docker"],
  "recommandation": "convoquer",
  "justification": "Le candidat possède une solide expérience en PHP/Laravel..."
}
```

**Règles** :
- `matching_score` : entier entre 0 et 100
- `recommandation` : une des trois valeurs (`convoquer`, `attente`, `rejeter`)
- L'IA ne doit pas inventer d'expérience, compétences ou diplômes
- Si le CV est ambigu, l'IA doit mentionner l'incertitude dans `justification`
- Les réponses invalides (JSON malformé, champs manquants) sont interceptées et loggées

### 6. Parsing et stockage

Le Job parse la réponse JSON et met à jour l'enregistrement `AnalyseCandidat` avec tous les champs structurés. Les tableaux sont stockés en JSON natif MySQL via les casts Eloquent.

### 7. Affichage

L'interface utilisateur affiche :
- **Score** (0-100) avec barre de progression
- **Recommandation** avec étiquette colorée (À convoquer / En attente / À rejeter)
- **Points forts** (liste)
- **Lacunes** (liste)
- **Compétences manquantes** (liste)
- **Justification** (texte)

## Suivi du statut d'analyse

| Statut | Description | Affichage |
|---|---|---|
| `pending` | En attente dans la file | Badge gris / "En attente" |
| `processing` | Analyse en cours | Badge bleu / spinner |
| `completed` | Analyse terminée | Badge vert / résultats |
| `failed` | Échec de l'analyse | Badge rouge / message d'erreur |

## Gestion des cas particuliers

| Cas | Comportement |
|---|---|
| **CV vide** | Rejeté avant dispatch du Job (validation formulaire) |
| **Offre sans compétences requises** | L'analyse note l'absence de prérequis et évalue le CV sur les critères disponibles |
| **Réponse IA invalide** (JSON malformé) | L'erreur est loggée, `statut_analyse = failed`, `message_erreur` renseigné |
| **Champs manquants dans la réponse IA** | Les champs manquants prennent des valeurs par défaut (tableau vide, score à 0) ; l'erreur est loggée |
| **Score hors limite** (0-100) | Le score est clampé entre 0 et 100 |
| **Candidat faible score** | L'analyse complète est retournée ; pas de rejet silencieux |

## Tests de l'IA

Dans les tests Pest, les appels IA sont **mockés / fakés** — aucun appel réel n'est effectué :

```php
// Exemple de structure de test
it('analyse un CV avec succès', function () {
    // Arrange: créer offre, candidat, dispatch job...
    // Assert: vérifier que statut_analyse = completed
    //         vérifier que matching_score est un entier
    //         vérifier que recommandation est valide
});
```

Pour utiliser l'IA réelle en développement, configurez dans `.env` :

```
AI_PROVIDER=openai
AI_MODEL=gpt-4o-mini
OPENAI_API_KEY=votre_clé_icitte
```
