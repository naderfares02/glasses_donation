<x-app-layout>
    <x-slot name="header">
        <div class="flex items-start justify-between gap-4">
            <div>
                <h2 class="font-semibold text-xl text-gray-800">Legal Pages</h2>
                <p class="text-sm text-gray-500 mt-1">Manage the Terms &amp; Conditions and Privacy Policy content.</p>
            </div>

            <a href="{{ route('admin.control') }}" class="text-sm font-semibold text-blue-700 hover:text-blue-800">
                ← Back
            </a>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if(session('success'))
                <div class="p-4 bg-green-50 border border-green-200 text-green-800 rounded-xl">
                    {{ session('success') }}
                </div>
            @endif

            <div class="bg-white border rounded-2xl shadow-sm overflow-hidden">
                <table class="w-full text-sm text-left">
                    <thead class="bg-gray-50 text-gray-600 uppercase text-xs">
                        <tr>
                            <th class="px-6 py-3">Page</th>
                            <th class="px-6 py-3">Key</th>
                            <th class="px-6 py-3">Status</th>
                            <th class="px-6 py-3">Last updated by</th>
                            <th class="px-6 py-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">

                        <tr>
                            <td class="px-6 py-4 font-semibold text-gray-900">Terms of Use</td>
                            <td class="px-6 py-4 text-gray-500">{{ $pages->key }}</td>
                            <td class="px-6 py-4">
                                @if($pages->published_at)
                                    <span
                                        class="inline-flex px-2.5 py-1 rounded-full text-xs font-semibold bg-green-50 text-green-700 border border-green-200">
                                        Published
                                    </span>
                                @else
                                    <span
                                        class="inline-flex px-2.5 py-1 rounded-full text-xs font-semibold bg-gray-50 text-gray-600 border border-gray-200">
                                        Draft
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-gray-500">
                                {{ $pages->editor?->name ?? '—' }}
                            </td>
                            <td class="px-6 py-4 text-right">
                                <a href="{{ route('admin.legal.edit', $pages) }}"
                                    class="inline-flex items-center px-4 py-2 rounded-xl text-sm font-semibold bg-blue-600 hover:bg-blue-700 text-white">
                                    Edit
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="px-6 py-10 text-center text-gray-500">
                                No legal pages found yet.
                            </td>
                        </tr>


                        <tr>
                            <td class="px-6 py-4 font-semibold text-gray-900">Privacy Policy</td>
                            <td class="px-6 py-4 text-gray-500">{{ $pages->key }}</td>
                            <td class="px-6 py-4">
                                @if($pages->published_at)
                                    <span
                                        class="inline-flex px-2.5 py-1 rounded-full text-xs font-semibold bg-green-50 text-green-700 border border-green-200">
                                        Published
                                    </span>
                                @else
                                    <span
                                        class="inline-flex px-2.5 py-1 rounded-full text-xs font-semibold bg-gray-50 text-gray-600 border border-gray-200">
                                        Draft
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-gray-500">
                                {{ $pages->editor?->name ?? '—' }}
                            </td>
                            <td class="px-6 py-4 text-right">
                                <a href="{{ route('admin.legal.edit', $pages) }}"
                                    class="inline-flex items-center px-4 py-2 rounded-xl text-sm font-semibold bg-blue-600 hover:bg-blue-700 text-white">
                                    Edit
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="px-6 py-10 text-center text-gray-500">
                                No legal pages found yet.
                            </td>
                        </tr>

                    </tbody>
                </table>
            </div>

        </div>
    </div>
</x-app-layout>