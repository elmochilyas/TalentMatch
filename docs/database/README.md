# Modèle de Données — TalentMatch

## Vue d'ensemble

```
users ──1:N──> offres ──1:N──> analyses_candidats <──N:1── candidats
```

## Tables

### `users`

Agents RH authentifiés via Laravel Breeze.

| Colonne | Type | Description |
|---|---|---|
| `id` | bigint unsigned (PK) | Identifiant unique |
| `name` | varchar(255) | Nom de l'utilisateur |
| `email` | varchar(255) | Adresse email (unique) |
| `email_verified_at` | timestamp (nullable) | Date de vérification |
| `password` | varchar(255) | Mot de passe hashé |
| `remember_token` | varchar(100) (nullable) | Token de session persistante |
| `created_at` | timestamp | Date de création |
| `updated_at` | timestamp | Date de modification |

### `offres`

Offres d'emploi créées par les agents RH.

| Colonne | Type | Description |
|---|---|---|
| `id` | bigint unsigned (PK) | Identifiant unique |
| `user_id` | bigint unsigned (FK → users) | Créateur de l'offre |
| `titre` | varchar(255) | Titre du poste |
| `description` | text | Description détaillée |
| `competences_requises` | json | Liste des compétences requises |
| `niveau_experience_minimum` | int | Années d'expérience minimales |
| `created_at` | timestamp | Date de création |
| `updated_at` | timestamp | Date de modification |

### `candidats`

Candidats ayant soumis leur CV.

| Colonne | Type | Description |
|---|---|---|
| `id` | bigint unsigned (PK) | Identifiant unique |
| `nom_candidat` | varchar(255) | Nom du candidat |
| `cv_texte` | longtext | Texte brut du CV soumis |
| `created_at` | timestamp | Date de création |
| `updated_at` | timestamp | Date de modification |

### `analyses_candidats`

Table pivot liant un candidat à une offre avec le résultat complet de l'analyse IA.

| Colonne | Type | Description |
|---|---|---|
| `id` | bigint unsigned (PK) | Identifiant unique |
| `offre_id` | bigint unsigned (FK → offres) | Offre concernée |
| `candidat_id` | bigint unsigned (FK → candidats) | Candidat concerné |
| `statut_analyse` | varchar(20) | Statut : `pending`, `processing`, `completed`, `failed` |
| `competences_extraites` | json | Compétences détectées par l'IA |
| `annees_experience` | smallint unsigned | Années d'expérience estimées |
| `niveau_etudes` | varchar(255) | Niveau d'études détecté |
| `langues` | json | Langues détectées |
| `matching_score` | tinyint unsigned | Score de matching (0-100) |
| `points_forts` | json | Points forts du candidat |
| `lacunes` | json | Lacunes identifiées |
| `competences_manquantes` | json | Compétences requises manquantes |
| `recommandation` | varchar(20) | Recommandation : `convoquer`, `attente`, `rejeter` |
| `justification` | text | Justification détaillée de l'analyse |
| `message_erreur` | text (nullable) | Message d'erreur si l'analyse a échoué |
| `created_at` | timestamp | Date de création |
| `updated_at` | timestamp | Date de modification |

## Relations (Eloquent)

- **User** `1:N` **Offre** — un agent RH peut créer plusieurs offres
- **Offre** `1:N` **AnalyseCandidat** — une offre reçoit plusieurs analyses
- **Candidat** `1:N` **AnalyseCandidat** — un candidat peut être analysé pour plusieurs offres
- **AnalyseCandidat** `N:1` **Offre** + `N:1` **Candidat** — table pivot avec attributs d'analyse

## Statuts d'analyse

| Valeur | Description |
|---|---|
| `pending` | En attente de traitement |
| `processing` | Analyse en cours par l'IA |
| `completed` | Analyse terminée avec succès |
| `failed` | Échec de l'analyse |

## Recommandations

| Valeur | Étiquette | Signification |
|---|---|---|
| `convoquer` | À convoquer | Candidat intéressant, à inviter |
| `attente` | En attente | Candidat moyen, à réévaluer |
| `rejeter` | À rejeter | Candidat ne correspondant pas |

## Mémoire des conversations

Les conversations avec l'assistant IA sont stockées dans les tables fournies par le SDK `laravel/ai` :

- `agent_conversations` — sessions de conversation
- `agent_conversation_messages` — messages échangés (avec métadonnées, appels d'outils)

Ces tables ne sont pas des tables personnalisées — elles sont gérées automatiquement par le SDK.
