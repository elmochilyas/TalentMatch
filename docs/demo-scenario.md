# Scénario de Démonstration — TalentMatch

Ce document décrit un parcours complet pour évaluer toutes les fonctionnalités principales de TalentMatch.

---

## Étape 1 : Inscription et connexion

1. Ouvrir l'application dans le navigateur
2. Cliquer sur **Register** (ou `/register`)
3. Remplir le formulaire : nom, email, mot de passe, confirmation
4. Cliquer sur **Register**
5. Vous êtes redirigé vers le tableau de bord

> ✅ **Résultat attendu** : Tableau de bord vide affiché avec un message "Aucune offre créée" ou similaire.

## Étape 2 : Créer une offre d'emploi

1. Cliquer sur **Créer une offre** (ou `/offres/create`)
2. Remplir les champs :
   - **Titre** : `Développeur Laravel`
   - **Description** : `Nous recherchons un développeur Laravel expérimenté pour rejoindre notre équipe.`
   - **Compétences requises** : `PHP, Laravel, MySQL, Git, API REST`
   - **Expérience minimale** : `3`
3. Cliquer sur **Créer**
4. Vous êtes redirigé vers la liste des offres

> ✅ **Résultat attendu** : L'offre "Développeur Laravel" apparaît dans la liste avec ses informations.

## Étape 3 : Soumettre un CV

1. Depuis la liste des offres, cliquer sur l'offre "Développeur Laravel"
2. Cliquer sur **Soumettre un CV** (ou lien d'analyse)
3. Remplir :
   - **Nom du candidat** : `Jean Dupont`
   - **Texte du CV** : (copier-coller un CV texte, par exemple ci-dessous)
4. Cliquer sur **Analyser**

**Exemple de CV à soumettre :**
```
Jean Dupont
Développeur PHP depuis 5 ans

Expérience :
- Développeur full-stack chez TechCorp (3 ans)
  PHP, Laravel, MySQL, Vue.js, Git, API REST
- Développeur junior chez WebStart (2 ans)
  PHP, JavaScript, HTML/CSS, PostgreSQL

Compétences : PHP, Laravel, MySQL, Git, API REST, Vue.js, Docker
Langues : Français (natif), Anglais (courant)
Formation : Master en Informatique, Université de Lyon
```

> ✅ **Résultat attendu** : Le statut passe à "pending", puis "processing". Après quelques secondes (ou immédiatement si la file est traitée), le statut passe à "completed" avec les résultats d'analyse.

## Étape 4 : Visualiser les résultats d'analyse

1. Depuis la page de détail de l'offre, cliquer sur l'analyse du candidat
2. Observer les informations affichées :

   - **Score de matching** (0-100)
   - **Recommandation** avec étiquette visible :
     - 🟢 À convoquer (score élevé)
     - 🟡 En attente (score moyen)
     - 🔴 À rejeter (score faible)
   - **Points forts** : liste des atouts du candidat
   - **Lacunes** : points faibles identifiés
   - **Compétences manquantes** : compétences requises non détectées
   - **Justification** : explication détaillée

> ✅ **Résultat attendu** : Tous les champs d'analyse sont affichés de manière structurée et lisible.

## Étape 5 : Interagir avec l'assistant conversationnel

1. Naviguer vers l'assistant (lien "Assistant" dans la navigation)
2. Poser les questions suivantes :

   **Question 1** — Analyse d'un candidat :
   ```
   Quelle est l'analyse du candidat Jean Dupont ?
   ```
   L'assistant utilise l'outil `getCandidateAnalysis` pour répondre avec les données réelles.

   **Question 2** — Prérequis d'une offre :
   ```
   Quelles sont les compétences requises pour le poste de Développeur Laravel ?
   ```
   L'assistant utilise l'outil `getJobRequirements` pour répondre.

   **Question 3** — Comparaison (si un deuxième candidat a été soumis) :
   ```
   Compare le candidat Jean Dupont avec [nom du second candidat].
   ```
   L'assistant utilise l'outil `compareCandidates` pour une comparaison côte à côte.

> ✅ **Résultat attendu** : L'assistant répond avec les données réelles issues des outils — il n'invente jamais d'informations.

## Étape 6 : (Optionnel) Comparaison de candidats

Si plusieurs candidats ont été soumis pour une même offre :

1. Depuis la page de détail de l'offre, cliquer sur **Comparer les candidats**
2. Sélectionner deux candidats
3. La vue comparative affiche les informations côte à côte

> ✅ **Résultat attendu** : Les scores, points forts, lacunes, et autres métriques sont alignés pour une comparaison facile.

---

## Points d'architecture à expliquer (pour l'évaluateur)

### Pourquoi OpenSpec avant le code ?
Chaque fonctionnalité commence par un spec validé pour éviter le scope creep et garantir l'intention métier. Les specs sont dans `openspec/specs/`.

### Pourquoi une file d'attente plutôt qu'une analyse synchrone ?
Les appels IA peuvent prendre 10 à 30 secondes. Une file d'attente (queue) permet à l'utilisateur de soumettre un CV et de continuer à travailler pendant que l'analyse se fait en arrière-plan. Le statut (`pending` → `processing` → `completed` / `failed`) permet de suivre la progression.

### Pourquoi une sortie structurée (JSON) plutôt que du texte libre ?
Le JSON structuré garantit des données fiables et parseables pour l'affichage, le tri, le filtrage et la comparaison. Le texte libre serait incohérent et difficile à exploiter.

### Pourquoi des outils plutôt que laisser l'IA deviner ?
Les outils Laravel (`getCandidateAnalysis`, `getJobRequirements`, `compareCandidates`) retournent de vraies données de la base. Sans outils, l'IA inventerait (hallucinerait) des profils de candidats.

### Pourquoi une mémoire de conversation ?
Le SDK `laravel/ai` gère la mémoire via ses propres tables de base de données. Cela permet des questions de suivi comme "et que penses-tu de cet autre candidat ?" sans perdre le contexte.

### Qu'est-ce qui a été généré par l'IA et qu'est-ce qui a été revu manuellement ?
Les specs, les décisions d'architecture et les règles de sécurité critiques sont revues manuellement. Le code de base (migrations suivant le MCD, squelettes de tests, templates Blade) peut être généré par l'IA.
