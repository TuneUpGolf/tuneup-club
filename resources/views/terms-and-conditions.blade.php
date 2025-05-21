@php
    $languages = \App\Facades\UtilityFacades::languages();
@endphp
@extends('layouts.main-landing')

@section('title', __('Terms & Conditions'))

@section('auth-topbar')
    <li class="language-btn">
        <select class="nice-select bg-gray-200 text-gray-700 p-2 rounded"
            onchange="this.options[this.selectedIndex].value && (window.location = this.options[this.selectedIndex].value);"
            id="language">
            @foreach ($languages as $language)
                <option class="" @if ($lang == $language) selected focus @endif
                    value="{{ route('change.lang', [$language]) }}">
                    {{ Str::upper($language) }}
                </option>
            @endforeach
        </select>
    </li>
@endsection

@section('content')
    <div class="bg-gray-100 min-h-screen py-10 px-4">
        <main class="max-w-4xl mx-auto bg-white shadow-lg rounded-lg p-6 mt-10 mb-10">
            <h1 class="text-3xl font-bold text-gray-900 border-b pb-4">Terms and Conditions for TuneUp LLC</h1>
            <p class="text-gray-700 mt-4"><strong>Effective Date:</strong> August 15th, 2024</p>

            <h2 class="text-xl font-semibold text-gray-800 mt-6">1. Introduction</h2>
            <p class="text-gray-700 mt-2">Welcome to TuneUp LLC (“we,” “our,” or “us”). These Terms and Conditions (“Terms”)
                govern your use of our application (the “App”), which is designed to streamline communication between
                golfers
                and influencers for lesson bookings and instruction. By accessing or using our App, you agree to these
                Terms.
                If you do not agree, please do not use our App.</p>

            <h2 class="text-xl font-semibold text-gray-800 mt-6">2. Use of the App</h2>
            <ul class="list-disc list-inside text-gray-700 mt-2 space-y-2">
                <li><strong>Eligibility:</strong> You must be at least 13 years old to use our App. By using the App, you
                    represent and warrant that you meet this age requirement.</li>
                <li><strong>Account:</strong> To access certain features, you may need to create an account. You are
                    responsible
                    for maintaining the confidentiality of your account information and for all activities that occur under
                    your account.</li>
            </ul>

            <h2 class="text-xl font-semibold text-gray-800 mt-6">3. User Responsibilities</h2>
            <ul class="list-disc list-inside text-gray-700 mt-2 space-y-2">
                <li><strong>Accuracy:</strong> You agree to provide accurate, current, and complete information during the
                    registration process and to update such information as necessary.</li>
                <li><strong>Prohibited Activities:</strong> You agree not to:
                    <ul class="list-disc list-inside ml-6 space-y-2">
                        <li>Use the App for any unlawful purpose.</li>
                        <li>Impersonate any person or entity, or falsely state or misrepresent your affiliation with any
                            person or entity.</li>
                        <li>Interfere with or disrupt the App or servers or networks connected to the App.</li>
                        <li>Attempt to gain unauthorized access to any part of the App or its related systems.</li>
                    </ul>
                </li>
            </ul>

            <h2 class="text-xl font-semibold text-gray-800 mt-6">4. Intellectual Property</h2>
            <p class="text-gray-700 mt-2"><strong>Ownership:</strong> All content, features, and functionality of the App
                are
                the exclusive property of TuneUp LLC and its licensors. You may not copy, modify, distribute, or create
                derivative works from any content provided in the App.</p>

            <h2 class="text-xl font-semibold text-gray-800 mt-6">5. Limitation of Liability</h2>
            <ul class="list-disc list-inside text-gray-700 mt-2 space-y-2">
                <li><strong>Disclaimer:</strong> The App is provided “as is” and “as available” without any warranties of
                    any
                    kind, whether express or implied. We do not warrant that the App will be uninterrupted or error-free.
                </li>
                <li><strong>Limitation:</strong> To the fullest extent permitted by law, TuneUp LLC shall not be liable for
                    any
                    indirect, incidental, special, consequential, or punitive damages arising out of or in connection with
                    your
                    use of the App.</li>
            </ul>

            <h2 class="text-xl font-semibold text-gray-800 mt-6">6. Termination</h2>
            <ul class="list-disc list-inside text-gray-700 mt-2 space-y-2">
                <li><strong>Termination by Us:</strong> We may suspend or terminate your access to the App if you violate
                    these
                    Terms or for any other reason at our sole discretion.</li>
                <li><strong>Termination by You:</strong> You may terminate your account at any time by contacting us at
                    <a href="mailto:support@tuneup.golf" class="text-blue-500 hover:underline">support@tuneup.golf</a>.
                </li>
            </ul>

            <h2 class="text-xl font-semibold text-gray-800 mt-6">7. Governing Law</h2>
            <p class="text-gray-700 mt-2">These Terms shall be governed by and construed in accordance with the laws of the
                state
                in which TuneUp LLC is based, without regard to its conflict of law principles.</p>

            <h2 class="text-xl font-semibold text-gray-800 mt-6">8. Changes to Terms</h2>
            <p class="text-gray-700 mt-2">We may modify these Terms from time to time. We will notify you of any changes by
                posting the new Terms on the App. Your continued use of the App after such modifications will constitute
                your
                acceptance of the new Terms.</p>

            <h2 class="text-xl font-semibold text-gray-800 mt-6">9. Contact Information</h2>
            <p class="text-gray-700 mt-2">If you have any questions or concerns about these Terms, please contact us at:
                <a href="mailto:support@tuneup.golf" class="text-blue-500 hover:underline">support@tuneup.golf</a>.
            </p>
        </main>
    </div>
@endsection
