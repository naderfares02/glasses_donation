<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Terms of Use</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-h-screen bg-gray-50">
    {{-- Top bar --}}
    <div class="bg-white border-b">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-5 flex items-center justify-between">
            <div>
                <p class="text-sm font-semibold text-blue-700">Legal</p>
                <h1 class="text-2xl font-extrabold text-gray-900">Terms of Use</h1>
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
                        {{-- Intro --}}
                        <section>
                            <h2 class="text-lg font-bold text-gray-900">1) Overview</h2>
                            <p class="mt-2 text-sm leading-7 text-gray-700">
                                These Terms of Use (“Terms”) govern your access to and use of our platform (the
                                “Service”).
                                By creating an account or using the Service, you agree to these Terms. If you do not
                                agree,
                                please do not use the Service.
                            </p>
                        </section>

                        {{-- Eligibility --}}
                        <section>
                            <h2 class="text-lg font-bold text-gray-900">2) Eligibility</h2>
                            <ul class="mt-3 text-sm leading-7 text-gray-700 list-disc pl-6 space-y-2">
                                <li>You must provide accurate information during registration (name, phone, city,
                                    email).</li>
                                <li>You are responsible for all activity under your account.</li>
                                <li>Admins may suspend accounts that violate these Terms.</li>
                            </ul>
                        </section>

                        {{-- Roles --}}
                        <section>
                            <h2 class="text-lg font-bold text-gray-900">3) Donors &amp; Recipients</h2>
                            <div class="mt-3 grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="p-4 rounded-2xl border bg-blue-50/40">
                                    <p class="text-sm font-bold text-gray-900">Donors</p>
                                    <ul class="mt-2 text-sm leading-7 text-gray-700 list-disc pl-6 space-y-1">
                                        <li>Provide truthful details about the glasses.</li>
                                        <li>Do not share sensitive data publicly in messages.</li>
                                        <li>Mark as donated only when delivery really happened.</li>
                                    </ul>
                                </div>

                                <div class="p-4 rounded-2xl border bg-green-50/40">
                                    <p class="text-sm font-bold text-gray-900">Recipients</p>
                                    <ul class="mt-2 text-sm leading-7 text-gray-700 list-disc pl-6 space-y-1">
                                        <li>Use the Service respectfully and honestly.</li>
                                        <li>Confirm or deny receiving donations accurately.</li>
                                        <li>Do not misuse contact requests or spam donors.</li>
                                    </ul>
                                </div>
                            </div>
                        </section>

                        {{-- Messaging --}}
                        <section>
                            <h2 class="text-lg font-bold text-gray-900">4) Messaging &amp; Conduct</h2>
                            <p class="mt-2 text-sm leading-7 text-gray-700">
                                You agree not to use the Service for harassment, fraud, threats, or illegal activities.
                                We may remove content or suspend accounts to keep the platform safe.
                            </p>
                        </section>

                        {{-- Donations --}}
                        <section>
                            <h2 class="text-lg font-bold text-gray-900">5) Donation Process</h2>
                            <ul class="mt-3 text-sm leading-7 text-gray-700 list-disc pl-6 space-y-2">
                                <li>Contact requests can be accepted/rejected by donors.</li>
                                <li>When a donor marks a donation as “donated”, a confirmation flow starts.</li>
                                <li>Admins may review and approve/reject donation requests as part of verification.</li>
                            </ul>
                        </section>

                        {{-- Privacy --}}
                        <section>
                            <h2 class="text-lg font-bold text-gray-900">6) Privacy</h2>
                            <p class="mt-2 text-sm leading-7 text-gray-700">
                                Our use of your personal information is described in the Privacy Policy.
                                By using the Service, you also agree to our Privacy Policy.
                            </p>
                        </section>

                        {{-- Termination --}}
                        <section>
                            <h2 class="text-lg font-bold text-gray-900">7) Suspension &amp; Termination</h2>
                            <p class="mt-2 text-sm leading-7 text-gray-700">
                                We may suspend or terminate your access if you violate these Terms, or for security and
                                operational reasons. Suspended users may not access the Service until reinstated.
                            </p>
                        </section>

                        {{-- Disclaimer --}}
                        <section>
                            <h2 class="text-lg font-bold text-gray-900">8) Disclaimer</h2>
                            <p class="mt-2 text-sm leading-7 text-gray-700">
                                The Service is provided “as is”. We do not guarantee delivery outcomes between donors
                                and recipients.
                                Always use caution when communicating and meeting.
                            </p>
                        </section>

                        {{-- Contact --}}
                        <section>
                            <h2 class="text-lg font-bold text-gray-900">9) Contact</h2>
                            <p class="mt-2 text-sm leading-7 text-gray-700">
                                If you have questions about these Terms, contact us through the platform support
                                channel.
                            </p>
                        </section>
                    </div>

                    {{-- Footer note --}}
                    <div class="mt-10 p-4 rounded-2xl border bg-gray-50 text-sm text-gray-700">
                        By continuing to use the Service, you acknowledge that you have read and understood these Terms.
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>