<x-app-layout>
    <x-slot name="header">
        <div class="flex items-start justify-between gap-4">
            <div>
                <h2 class="font-semibold text-xl text-gray-800">Edit: {{ $page->title }}</h2>
                <p class="text-sm text-gray-500 mt-1">Update content safely and publish when ready.</p>
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

            <form method="POST" action="{{ route('admin.legal.update', $page->id) }}" class="space-y-6">
                @csrf
                @method('PUT')

                <div class="bg-white border rounded-2xl shadow-sm p-6 space-y-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            Content (HTML)
                        </label>

                        <textarea name="content" rows="16"
                            class="w-full border rounded-xl px-4 py-3 text-sm focus:ring-2 focus:ring-blue-200 font-mono"
                            placeholder="Write HTML...">{{ old('content', $page->content) }}</textarea>

                        @error('content') <p class="text-sm text-red-600 mt-2">{{ $message }}</p> @enderror

                        <p class="text-xs text-gray-500 mt-2">
                            Tip: Include your own heading inside the content (e.g. &lt;header class="page-head"&gt;&lt;h1&gt;...&lt;/h1&gt;&lt;/header&gt;) —
                            the page no longer shows a separate title bar, so the heading must be part of your HTML.
                        </p>
                    </div>

                    <label class="inline-flex items-center gap-2">
                        <input type="checkbox" name="publish" value="1"
                            class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                        <span class="text-sm text-gray-700 font-semibold">
                            Publish now (updates published_at)
                        </span>
                    </label>

                    <div class="flex items-center justify-end gap-3 pt-2">
                        <a href="{{ $page->key === 'terms' ? route('terms') : route('privacy') }}"
                            class="px-4 py-2 rounded-xl text-sm font-semibold border bg-white hover:bg-gray-50">
                            View page
                        </a>

                        <button type="submit"
                            class="px-5 py-2.5 rounded-xl text-sm font-semibold bg-blue-600 hover:bg-blue-700 text-white">
                            Save changes
                        </button>
                    </div>
                </div>
            </form>

        </div>
    </div>
</x-app-layout>