<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Modifier') }} — {{ $offre->titre }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form method="POST" action="{{ route('offres.update', $offre) }}">
                        @csrf
                        @method('PUT')

                        <div class="mb-4">
                            <label for="titre" class="block text-sm font-medium text-gray-700">{{ __('Titre') }}</label>
                            <input id="titre" type="text" name="titre" value="{{ old('titre', $offre->titre) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
                            @error('titre')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="description" class="block text-sm font-medium text-gray-700">{{ __('Description') }}</label>
                            <textarea id="description" name="description" rows="5" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">{{ old('description', $offre->description) }}</textarea>
                            @error('description')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="competences_requises" class="block text-sm font-medium text-gray-700">{{ __('Compétences requises') }}</label>
                            <input id="competences_requises" type="text" name="competences_requises" value="{{ old('competences_requises', is_array($offre->competences_requises) ? implode(', ', $offre->competences_requises) : $offre->competences_requises) }}" placeholder="PHP, JavaScript, Laravel" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
                            <p class="mt-1 text-xs text-gray-500">{{ __('Séparez les compétences par des virgules.') }}</p>
                            @error('competences_requises')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="niveau_experience_minimum" class="block text-sm font-medium text-gray-700">{{ __('Expérience minimum (années)') }}</label>
                            <input id="niveau_experience_minimum" type="number" name="niveau_experience_minimum" value="{{ old('niveau_experience_minimum', $offre->niveau_experience_minimum) }}" min="0" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
                            @error('niveau_experience_minimum')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="flex items-center gap-4">
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                                {{ __('Mettre à jour') }}
                            </button>
                            <a href="{{ route('offres.show', $offre) }}" class="text-sm text-gray-600 hover:text-gray-900">{{ __('Annuler') }}</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
