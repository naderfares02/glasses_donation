<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Privacy Policy</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen bg-gray-50">
    {{-- Top bar --}}
    <div class="bg-white border-b">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-5 flex items-center justify-between">
            <div>
                <p class="text-sm font-semibold text-blue-700">Legal</p>
                <h1 class="text-2xl font-extrabold text-gray-900">Privacy Policy</h1>
            </div>

            <a href="{{ url()->previous() }}"
                class="inline-flex items-center gap-2 px-4 py-2 rounded-xl border bg-white hover:bg-gray-50 text-sm font-semibold text-gray-800">
                ← Back
            </a>
        </div>
    </div>

    {{-- Content --}}
    <div class="py-10">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white border rounded-3xl shadow-sm overflow-hidden">
                <div class="p-6 sm:p-10">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                        <p class="text-sm text-gray-600">
                            Last updated: <span class="font-semibold">{{ now()->format('Y-m-d') }}</span>
                        </p>
                        <div
                            class="inline-flex items-center gap-2 text-xs font-semibold px-3 py-1 rounded-full border bg-gray-50 text-gray-700">
                            Version 1.0
                        </div>
                    </div>

                    <div class="mt-8 space-y-8 text-gray-800">
                        <section>
                            <h2 class="text-lg font-bold text-gray-900">1) What we collect</h2>
                            <ul class="mt-3 text-sm leading-7 text-gray-700 list-disc pl-6 space-y-2">
                                <li><span class="font-semibold">Account data:</span> name, email, phone number, city,
                                    role (donor/recipient).</li>
                                <li><span class="font-semibold">Content:</span> messages, donation notes, and
                                    confirmation notes you submit.</li>
                                <li><span class="font-semibold">Usage data:</span> basic logs needed for security and
                                    debugging (e.g., timestamps).</li>
                            </ul>
                        </section>

                        <section>
                            <h2 class="text-lg font-bold text-gray-900">2) Why we use it</h2>
                            <ul class="mt-3 text-sm leading-7 text-gray-700 list-disc pl-6 space-y-2">
                                <li>To create and manage accounts.</li>
                                <li>To enable messaging between donors and recipients.</li>
                                <li>To support donation confirmation and admin review.</li>
                                <li>To prevent abuse, fraud, or spam and keep the platform safe.</li>
                            </ul>
                        </section>

                        <section>
                            <h2 class="text-lg font-bold text-gray-900">3) Sharing of information</h2>
                            <p class="mt-2 text-sm leading-7 text-gray-700">
                                We do not sell your personal information. We may share information only when necessary
                                to operate
                                the Service (e.g., displaying donor/recipient names in the donation flow) or to comply
                                with legal requests.
                            </p>

                            <div class="mt-4 p-4 rounded-2xl border bg-amber-50/60">
                                <p class="text-sm font-semibold text-amber-900">Important</p>
                                <p class="mt-1 text-sm text-amber-800 leading-7">
                                    Avoid sharing sensitive personal information in chat messages. Admins may review
                                    reports for safety.
                                </p>
                            </div>
                        </section>

                        <section>
                            <h2 class="text-lg font-bold text-gray-900">4) Data retention</h2>
                            <p class="mt-2 text-sm leading-7 text-gray-700">
                                We keep your data only as long as needed to provide the Service, comply with legal
                                obligations,
                                and maintain security. You may request account deletion where applicable.
                            </p>
                        </section>

                        <section>
                            <h2 class="text-lg font-bold text-gray-900">5) Security</h2>
                            <p class="mt-2 text-sm leading-7 text-gray-700">
                                We use reasonable measures to protect your data, but no system is 100% secure.
                                Use a strong password and keep it confidential.
                            </p>
                        </section>

                        <section>
                            <h2 class="text-lg font-bold text-gray-900">6) Your choices</h2>
                            <ul class="mt-3 text-sm leading-7 text-gray-700 list-disc pl-6 space-y-2">
                                <li>You can update your profile information (where the platform allows).</li>
                                <li>You can request support regarding your account.</li>
                                <li>Admins can suspend accounts that violate rules.</li>
                            </ul>
                        </section>

                        <section>
                            <h2 class="text-lg font-bold text-gray-900">7) Contact</h2>
                            <p class="mt-2 text-sm leading-7 text-gray-700">
                                If you have questions about this Privacy Policy, contact us through the platform support
                                channel.
                            </p>
                        </section>
                    </div>

                    <div class="mt-10 p-4 rounded-2xl border bg-gray-50 text-sm text-gray-700">
                        By using the Service, you acknowledge that you have read and understood this Privacy Policy.
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>