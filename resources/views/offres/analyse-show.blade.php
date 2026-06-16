<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ $analyse->candidat->nom_candidat }} — {{ $offre->titre }}
            </h2>
            <a href="{{ route('offres.show', $offre) }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-50">
                {{ __('Retour à l\'offre') }}
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if ($analyse->statut_analyse->value === 'failed')
                <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-md">
                    <h3 class="text-sm font-medium text-red-800">{{ __('Analyse échouée') }}</h3>
                    @if ($analyse->message_erreur)
                        <p class="mt-1 text-sm text-red-600">{{ $analyse->message_erreur }}</p>
                    @endif
                </div>
            @endif

            @if ($analyse->statut_analyse->value === 'pending' || $analyse->statut_analyse->value === 'processing')
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 text-center">
                        <div class="inline-flex items-center gap-2">
                            <svg class="animate-spin h-5 w-5 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                            </svg>
                            <span class="text-blue-600 font-medium">
                                {{ $analyse->statut_analyse->value === 'processing' ? __('Analyse en cours...') : __('Analyse en attente...') }}
                            </span>
                        </div>
                        <p class="mt-2 text-sm text-gray-500">{{ __('Revenez dans quelques instants pour voir les résultats.') }}</p>
                    </div>
                </div>
            @elseif ($analyse->statut_analyse->value === 'completed')
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 text-center">
                            <div class="text-4xl font-bold {{ $analyse->matching_score >= 75 ? 'text-green-600' : ($analyse->matching_score >= 50 ? 'text-yellow-600' : 'text-red-600') }}">
                                {{ $analyse->matching_score }}<span class="text-lg">/100</span>
                            </div>
                            <div class="mt-1 text-sm text-gray-500">{{ __('Score de correspondance') }}</div>
                        </div>
                    </div>

                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 text-center">
                            @php
                                $recClass = match($analyse->recommandation?->value) {
                                    'convoquer' => 'text-green-600',
                                    'attente' => 'text-yellow-600',
                                    'rejeter' => 'text-red-600',
                                    default => 'text-gray-400'
                                };
                                $recLabel = match($analyse->recommandation?->value) {
                                    'convoquer' => __('À convoquer'),
                                    'attente' => __('En attente'),
                                    'rejeter' => __('À rejeter'),
                                    default => '—'
                                };
                            @endphp
                            <div class="text-2xl font-bold {{ $recClass }}">
                                {{ $recLabel }}
                            </div>
                            <div class="mt-1 text-sm text-gray-500">{{ __('Recommandation') }}</div>
                        </div>
                    </div>

                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 text-center">
                            <div class="text-2xl font-bold text-gray-900">
                                {{ $analyse->annees_experience ?? '—' }}
                            </div>
                            <div class="mt-1 text-sm text-gray-500">{{ __('Années d\'expérience') }}</div>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wider">{{ __('Compétences extraites') }}</h3>
                            @if ($analyse->competences_extraites)
                                <div class="mt-2 flex flex-wrap gap-2">
                                    @foreach ($analyse->competences_extraites as $skill)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">{{ $skill }}</span>
                                    @endforeach
                                </div>
                            @else
                                <p class="mt-2 text-sm text-gray-400">—</p>
                            @endif
                        </div>
                    </div>

                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wider">{{ __('Langues') }}</h3>
                            @if ($analyse->langues)
                                <div class="mt-2 flex flex-wrap gap-2">
                                    @foreach ($analyse->langues as $langue)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">{{ $langue }}</span>
                                    @endforeach
                                </div>
                            @else
                                <p class="mt-2 text-sm text-gray-400">—</p>
                            @endif
                        </div>
                    </div>

                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wider">{{ __('Niveau d\'études') }}</h3>
                            <p class="mt-2 text-sm text-gray-900">{{ $analyse->niveau_etudes ?? '—' }}</p>
                        </div>
                    </div>

                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wider">{{ __('Points forts') }}</h3>
                            @if ($analyse->points_forts)
                                <ul class="mt-2 space-y-1">
                                    @foreach ($analyse->points_forts as $point)
                                        <li class="text-sm text-gray-900 flex items-start gap-2">
                                            <span class="text-green-500 mt-0.5">✓</span>
                                            {{ $point }}
                                        </li>
                                    @endforeach
                                </ul>
                            @else
                                <p class="mt-2 text-sm text-gray-400">—</p>
                            @endif
                        </div>
                    </div>

                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wider">{{ __('Lacunes') }}</h3>
                            @if ($analyse->lacunes)
                                <ul class="mt-2 space-y-1">
                                    @foreach ($analyse->lacunes as $lacune)
                                        <li class="text-sm text-gray-900 flex items-start gap-2">
                                            <span class="text-red-500 mt-0.5">✗</span>
                                            {{ $lacune }}
                                        </li>
                                    @endforeach
                                </ul>
                            @else
                                <p class="mt-2 text-sm text-gray-400">—</p>
                            @endif
                        </div>
                    </div>

                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wider">{{ __('Compétences manquantes') }}</h3>
                            @if ($analyse->competences_manquantes)
                                <div class="mt-2 flex flex-wrap gap-2">
                                    @foreach ($analyse->competences_manquantes as $skill)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800">{{ $skill }}</span>
                                    @endforeach
                                </div>
                            @else
                                <p class="mt-2 text-sm text-gray-400">—</p>
                            @endif
                        </div>
                    </div>

                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg md:col-span-2">
                        <div class="p-6">
                            <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wider">{{ __('Justification') }}</h3>
                            <p class="mt-2 text-sm text-gray-900 whitespace-pre-wrap">{{ $analyse->justification ?? '—' }}</p>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
