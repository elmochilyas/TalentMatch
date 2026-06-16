<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ $offre->titre }}
            </h2>
            <div class="flex gap-2">
                <a href="{{ route('offres.edit', $offre) }}" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                    {{ __('Modifier') }}
                </a>
                <a href="{{ route('offres.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-50">
                    {{ __('Retour') }}
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    @if (session('success'))
                        <div class="mb-4 p-4 bg-green-50 border border-green-200 rounded-md text-sm text-green-700">
                            {{ session('success') }}
                        </div>
                    @endif

                    <dl class="grid grid-cols-1 gap-4">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">{{ __('Description') }}</dt>
                            <dd class="mt-1 text-sm text-gray-900 whitespace-pre-wrap">{{ $offre->description }}</dd>
                        </div>

                        <div>
                            <dt class="text-sm font-medium text-gray-500">{{ __('Expérience minimum') }}</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $offre->niveau_experience_minimum ?? '—' }} {{ $offre->niveau_experience_minimum ? 'année(s)' : '' }}</dd>
                        </div>

                        <div>
                            <dt class="text-sm font-medium text-gray-500">{{ __('Compétences requises') }}</dt>
                            <dd class="mt-1 text-sm text-gray-900">
                                @if ($offre->competences_requises)
                                    <div class="flex flex-wrap gap-2">
                                        @foreach ($offre->competences_requises as $skill)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                {{ $skill }}
                                            </span>
                                        @endforeach
                                    </div>
                                @else
                                    <span class="text-gray-400">{{ __('Aucune') }}</span>
                                @endif
                            </dd>
                        </div>

                        <div>
                            <dt class="text-sm font-medium text-gray-500">{{ __('Candidatures') }}</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $offre->analysesCandidats->count() }}</dd>
                        </div>
                    </dl>
                </div>
            </div>

            <div class="mt-6 bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-semibold mb-4">{{ __('Soumettre une candidature') }}</h3>

                    <form method="POST" action="{{ route('offres.candidatures.store', $offre) }}">
                        @csrf

                        <div class="mb-4">
                            <label for="nom_candidat" class="block text-sm font-medium text-gray-700">{{ __('Nom du candidat') }}</label>
                            <input id="nom_candidat" type="text" name="nom_candidat" value="{{ old('nom_candidat') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
                            @error('nom_candidat')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="cv_texte" class="block text-sm font-medium text-gray-700">{{ __('Texte du CV') }}</label>
                            <textarea id="cv_texte" name="cv_texte" rows="8" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('cv_texte') }}</textarea>
                            @error('cv_texte')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                            {{ __('Soumettre pour analyse') }}
                        </button>
                    </form>
                </div>
            </div>

            <div class="mt-6 bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-semibold mb-4">{{ __('Candidatures soumises') }}</h3>

                    @if ($offre->analysesCandidats->isEmpty())
                        <p class="text-gray-500">{{ __('Aucune candidature soumise pour le moment.') }}</p>
                    @else
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Candidat') }}</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Statut') }}</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Score') }}</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Recommandation') }}</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ __('Date') }}</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach ($offre->analysesCandidats as $analyse)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                                            <a href="{{ route('offres.analyses.show', [$offre, $analyse]) }}" class="text-blue-600 hover:underline">
                                                {{ $analyse->candidat->nom_candidat }}
                                            </a>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @php
                                                $statusValue = $analyse->statut_analyse?->value ?? 'pending';
                                                $statusClasses = [
                                                    'pending' => 'bg-yellow-100 text-yellow-800',
                                                    'processing' => 'bg-blue-100 text-blue-800',
                                                    'completed' => 'bg-green-100 text-green-800',
                                                    'failed' => 'bg-red-100 text-red-800',
                                                ];
                                                $statusLabels = [
                                                    'pending' => __('En attente'),
                                                    'processing' => __('En cours'),
                                                    'completed' => __('Terminé'),
                                                    'failed' => __('Échoué'),
                                                ];
                                            @endphp
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusClasses[$statusValue] ?? 'bg-gray-100 text-gray-800' }}">
                                                {{ $statusLabels[$statusValue] ?? $statusValue }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ $analyse->matching_score ?? '—' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            @php
                                                $recValue = $analyse->recommandation?->value;
                                            @endphp
                                            @if ($recValue === 'convoquer')
                                                <span class="text-green-600 font-medium">{{ __('À convoquer') }}</span>
                                            @elseif ($recValue === 'attente')
                                                <span class="text-yellow-600 font-medium">{{ __('En attente') }}</span>
                                            @elseif ($recValue === 'rejeter')
                                                <span class="text-red-600 font-medium">{{ __('À rejeter') }}</span>
                                            @else
                                                <span class="text-gray-400">—</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $analyse->created_at->format('d/m/Y') }}
                                        </td>
                                    </tr>
                                    @if ($statusValue === 'failed' && $analyse->message_erreur)
                                        <tr>
                                            <td colspan="5" class="px-6 py-2 text-sm text-red-600 bg-red-50">
                                                {{ $analyse->message_erreur }}
                                            </td>
                                        </tr>
                                    @endif
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>
            </div>

            <div class="mt-6 bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form method="POST" action="{{ route('offres.destroy', $offre) }}" onsubmit="return confirm('{{ __('Supprimer cette offre ?') }}')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-sm text-red-600 hover:text-red-900">{{ __('Supprimer cette offre') }}</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
