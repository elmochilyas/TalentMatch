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
                            <dd class="mt-1 text-sm text-gray-900">0</dd>
                        </div>
                    </dl>

                    <div class="mt-6 pt-6 border-t border-gray-200">
                        <form method="POST" action="{{ route('offres.destroy', $offre) }}" onsubmit="return confirm('{{ __('Supprimer cette offre ?') }}')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-sm text-red-600 hover:text-red-900">{{ __('Supprimer cette offre') }}</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
