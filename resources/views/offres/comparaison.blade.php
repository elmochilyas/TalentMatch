<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Comparer des candidats') }} — {{ $offre->titre }}
            </h2>
            <a href="{{ route('offres.show', $offre) }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-50">
                {{ __('Retour à l\'offre') }}
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="mb-4 p-4 bg-green-50 border border-green-200 rounded-md text-sm text-green-700">
                    {{ session('success') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-md text-sm text-red-700">
                    {{ $errors->first('message') }}
                </div>
            @endif

            @if (!isset($analyse1) || !isset($analyse2))
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <form method="POST" action="{{ route('offres.comparaison.compare', $offre) }}">
                            @csrf

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label for="analyse_id_1" class="block text-sm font-medium text-gray-700">{{ __('Premier candidat') }}</label>
                                    <select id="analyse_id_1" name="analyse_id_1" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                        <option value="">{{ __('Sélectionnez un candidat') }}</option>
                                        @foreach ($offre->analysesCandidats as $a)
                                            <option value="{{ $a->id }}" {{ old('analyse_id_1') == $a->id ? 'selected' : '' }}>
                                                {{ $a->candidat->nom_candidat }} ({{ $a->matching_score ?? '—' }}/100)
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('analyse_id_1')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="analyse_id_2" class="block text-sm font-medium text-gray-700">{{ __('Second candidat') }}</label>
                                    <select id="analyse_id_2" name="analyse_id_2" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                        <option value="">{{ __('Sélectionnez un candidat') }}</option>
                                        @foreach ($offre->analysesCandidats as $a)
                                            <option value="{{ $a->id }}" {{ old('analyse_id_2') == $a->id ? 'selected' : '' }}>
                                                {{ $a->candidat->nom_candidat }} ({{ $a->matching_score ?? '—' }}/100)
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('analyse_id_2')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            <div class="mt-6">
                                <button type="submit" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                                    {{ __('Comparer') }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            @else
                @php
                    $status1 = $analyse1->statut_analyse->value;
                    $status2 = $analyse2->statut_analyse->value;
                    $bothCompleted = $status1 === 'completed' && $status2 === 'completed';
                @endphp

                @if (!$bothCompleted)
                    <div class="mb-6 p-4 bg-yellow-50 border border-yellow-200 rounded-md text-sm text-yellow-700">
                        {{ __('Comparaison limitée : les deux analyses doivent être terminées pour afficher les données complètes.') }}
                        <ul class="mt-2 list-disc list-inside">
                            <li>{{ $analyse1->candidat->nom_candidat }} : {{ __("Statut") }} — {{ $status1 }}</li>
                            <li>{{ $analyse2->candidat->nom_candidat }} : {{ __("Statut") }} — {{ $status2 }}</li>
                        </ul>
                    </div>
                @else
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="p-6">
                                <h3 class="text-lg font-semibold mb-2">{{ $analyse1->candidat->nom_candidat }}</h3>

                                <dl class="space-y-4">
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">{{ __('Score') }}</dt>
                                        <dd class="mt-1 text-2xl font-bold {{ $analyse1->matching_score >= 75 ? 'text-green-600' : ($analyse1->matching_score >= 50 ? 'text-yellow-600' : 'text-red-600') }}">
                                            {{ $analyse1->matching_score ?? '—' }}/100
                                        </dd>
                                    </div>

                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">{{ __('Recommandation') }}</dt>
                                        <dd class="mt-1 font-medium">
                                            @php
                                                $recLabel1 = match($analyse1->recommandation?->value) {
                                                    'convoquer' => __('À convoquer'),
                                                    'attente' => __('En attente'),
                                                    'rejeter' => __('À rejeter'),
                                                    default => '—'
                                                };
                                                $recClass1 = match($analyse1->recommandation?->value) {
                                                    'convoquer' => 'text-green-600',
                                                    'attente' => 'text-yellow-600',
                                                    'rejeter' => 'text-red-600',
                                                    default => 'text-gray-400'
                                                };
                                            @endphp
                                            <span class="{{ $recClass1 }}">{{ $recLabel1 }}</span>
                                        </dd>
                                    </div>

                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">{{ __('Compétences extraites') }}</dt>
                                        <dd class="mt-1">
                                            @if ($analyse1->competences_extraites)
                                                <div class="flex flex-wrap gap-1">
                                                    @foreach ($analyse1->competences_extraites as $s)
                                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">{{ $s }}</span>
                                                    @endforeach
                                                </div>
                                            @else
                                                <span class="text-gray-400">{{ __('Non disponibles') }}</span>
                                            @endif
                                        </dd>
                                    </div>

                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">{{ __('Points forts') }}</dt>
                                        <dd class="mt-1">
                                            @if ($analyse1->points_forts)
                                                <ul class="space-y-1">
                                                    @foreach ($analyse1->points_forts as $p)
                                                        <li class="text-sm text-gray-900 flex items-start gap-1">
                                                            <span class="text-green-500 mt-0.5">✓</span>
                                                            {{ $p }}
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            @else
                                                <span class="text-gray-400">{{ __('Non disponibles') }}</span>
                                            @endif
                                        </dd>
                                    </div>

                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">{{ __('Lacunes') }}</dt>
                                        <dd class="mt-1">
                                            @if ($analyse1->lacunes)
                                                <ul class="space-y-1">
                                                    @foreach ($analyse1->lacunes as $l)
                                                        <li class="text-sm text-gray-900 flex items-start gap-1">
                                                            <span class="text-red-500 mt-0.5">✗</span>
                                                            {{ $l }}
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            @else
                                                <span class="text-gray-400">{{ __('Non disponibles') }}</span>
                                            @endif
                                        </dd>
                                    </div>

                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">{{ __('Compétences manquantes') }}</dt>
                                        <dd class="mt-1">
                                            @if ($analyse1->competences_manquantes)
                                                <div class="flex flex-wrap gap-1">
                                                    @foreach ($analyse1->competences_manquantes as $s)
                                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800">{{ $s }}</span>
                                                    @endforeach
                                                </div>
                                            @else
                                                <span class="text-gray-400">{{ __('Non disponibles') }}</span>
                                            @endif
                                        </dd>
                                    </div>

                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">{{ __('Justification') }}</dt>
                                        <dd class="mt-1 text-sm text-gray-900 whitespace-pre-wrap">{{ $analyse1->justification ?? '—' }}</dd>
                                    </div>
                                </dl>
                            </div>
                        </div>

                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                            <div class="p-6">
                                <h3 class="text-lg font-semibold mb-2">{{ $analyse2->candidat->nom_candidat }}</h3>

                                <dl class="space-y-4">
                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">{{ __('Score') }}</dt>
                                        <dd class="mt-1 text-2xl font-bold {{ $analyse2->matching_score >= 75 ? 'text-green-600' : ($analyse2->matching_score >= 50 ? 'text-yellow-600' : 'text-red-600') }}">
                                            {{ $analyse2->matching_score ?? '—' }}/100
                                        </dd>
                                    </div>

                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">{{ __('Recommandation') }}</dt>
                                        <dd class="mt-1 font-medium">
                                            @php
                                                $recLabel2 = match($analyse2->recommandation?->value) {
                                                    'convoquer' => __('À convoquer'),
                                                    'attente' => __('En attente'),
                                                    'rejeter' => __('À rejeter'),
                                                    default => '—'
                                                };
                                                $recClass2 = match($analyse2->recommandation?->value) {
                                                    'convoquer' => 'text-green-600',
                                                    'attente' => 'text-yellow-600',
                                                    'rejeter' => 'text-red-600',
                                                    default => 'text-gray-400'
                                                };
                                            @endphp
                                            <span class="{{ $recClass2 }}">{{ $recLabel2 }}</span>
                                        </dd>
                                    </div>

                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">{{ __('Compétences extraites') }}</dt>
                                        <dd class="mt-1">
                                            @if ($analyse2->competences_extraites)
                                                <div class="flex flex-wrap gap-1">
                                                    @foreach ($analyse2->competences_extraites as $s)
                                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">{{ $s }}</span>
                                                    @endforeach
                                                </div>
                                            @else
                                                <span class="text-gray-400">{{ __('Non disponibles') }}</span>
                                            @endif
                                        </dd>
                                    </div>

                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">{{ __('Points forts') }}</dt>
                                        <dd class="mt-1">
                                            @if ($analyse2->points_forts)
                                                <ul class="space-y-1">
                                                    @foreach ($analyse2->points_forts as $p)
                                                        <li class="text-sm text-gray-900 flex items-start gap-1">
                                                            <span class="text-green-500 mt-0.5">✓</span>
                                                            {{ $p }}
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            @else
                                                <span class="text-gray-400">{{ __('Non disponibles') }}</span>
                                            @endif
                                        </dd>
                                    </div>

                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">{{ __('Lacunes') }}</dt>
                                        <dd class="mt-1">
                                            @if ($analyse2->lacunes)
                                                <ul class="space-y-1">
                                                    @foreach ($analyse2->lacunes as $l)
                                                        <li class="text-sm text-gray-900 flex items-start gap-1">
                                                            <span class="text-red-500 mt-0.5">✗</span>
                                                            {{ $l }}
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            @else
                                                <span class="text-gray-400">{{ __('Non disponibles') }}</span>
                                            @endif
                                        </dd>
                                    </div>

                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">{{ __('Compétences manquantes') }}</dt>
                                        <dd class="mt-1">
                                            @if ($analyse2->competences_manquantes)
                                                <div class="flex flex-wrap gap-1">
                                                    @foreach ($analyse2->competences_manquantes as $s)
                                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800">{{ $s }}</span>
                                                    @endforeach
                                                </div>
                                            @else
                                                <span class="text-gray-400">{{ __('Non disponibles') }}</span>
                                            @endif
                                        </dd>
                                    </div>

                                    <div>
                                        <dt class="text-sm font-medium text-gray-500">{{ __('Justification') }}</dt>
                                        <dd class="mt-1 text-sm text-gray-900 whitespace-pre-wrap">{{ $analyse2->justification ?? '—' }}</dd>
                                    </div>
                                </dl>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                        <div class="p-6">
                            <h3 class="text-lg font-semibold mb-4">{{ __('Conclusion') }}</h3>
                            @php
                                $s1 = $analyse1->matching_score;
                                $s2 = $analyse2->matching_score;
                            @endphp
                            @if ($s1 !== null && $s2 !== null)
                                @if ($s1 > $s2)
                                    <p class="text-gray-900">
                                        {{ __('Sur la base des analyses sauvegardées, :nom1 (score : :score1/100) est plus adapté(e) que :nom2 (score : :score2/100) pour cette offre.', [
                                            'nom1' => $analyse1->candidat->nom_candidat,
                                            'score1' => $s1,
                                            'nom2' => $analyse2->candidat->nom_candidat,
                                            'score2' => $s2,
                                        ]) }}
                                    </p>
                                @elseif ($s2 > $s1)
                                    <p class="text-gray-900">
                                        {{ __('Sur la base des analyses sauvegardées, :nom1 (score : :score1/100) est plus adapté(e) que :nom2 (score : :score2/100) pour cette offre.', [
                                            'nom1' => $analyse2->candidat->nom_candidat,
                                            'score1' => $s2,
                                            'nom2' => $analyse1->candidat->nom_candidat,
                                            'score2' => $s1,
                                        ]) }}
                                    </p>
                                @else
                                    <p class="text-gray-900">
                                        {{ __('Les deux candidats ont le même score (:score/100). Consultez les détails ci-dessus pour évaluer les forces et lacunes respectives.', [
                                            'score' => $s1,
                                        ]) }}
                                    </p>
                                @endif
                            @else
                                <p class="text-gray-500">{{ __('Les scores ne sont pas disponibles pour les deux analyses. La comparaison est limitée.') }}</p>
                            @endif
                        </div>
                    </div>
                @endif

                <div class="mt-4">
                    <a href="{{ route('offres.comparaison.index', $offre) }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-50">
                        {{ __('Comparer d\'autres candidats') }}
                    </a>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
