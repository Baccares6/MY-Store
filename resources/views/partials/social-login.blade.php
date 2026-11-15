{{--
    Tombol Login/Register dengan Google & Facebook.
    Hanya penyedia yang Client ID & Secret-nya sudah diisi di .env yang ditampilkan.
--}}
@php($socialProviders = collect(\App\Http\Controllers\SocialAuthController::PROVIDERS)->keys()->filter(fn ($p) => \App\Http\Controllers\SocialAuthController::isConfigured($p)))

@if ($socialProviders->isNotEmpty())
    <style>
        .social-divider { display: flex; align-items: center; gap: 12px; margin: 22px 0 16px; font-size: 12px; color: #94a3b8; }
        .social-divider::before, .social-divider::after { content: ""; flex: 1; height: 1px; background: #e2e8f0; }
        .social-buttons { display: flex; flex-direction: column; gap: 10px; }
        .btn-social {
            display: flex; align-items: center; justify-content: center; gap: 10px;
            width: 100%; padding: 12px; font-family: inherit; font-size: 14px; font-weight: 600;
            border-radius: 10px; text-decoration: none; transition: background-color .15s, border-color .15s;
        }
        .btn-social svg { width: 18px; height: 18px; flex-shrink: 0; }
        .btn-google { color: #1f1f1f; background: #ffffff; border: 1.5px solid #dadce0; }
        .btn-google:hover { background: #f8fafc; border-color: #c4c7c5; }
        .btn-facebook { color: #ffffff; background: #1877f2; border: 1.5px solid #1877f2; }
        .btn-facebook:hover { background: #166fe5; }
    </style>

    <div class="social-divider">or</div>

    <div class="social-buttons">
        @if ($socialProviders->contains('google'))
            <a href="{{ route('social.redirect', 'google') }}" class="btn-social btn-google">
                <svg viewBox="0 0 48 48" aria-hidden="true">
                    <path fill="#EA4335" d="M24 9.5c3.54 0 6.71 1.22 9.21 3.6l6.85-6.85C35.9 2.38 30.47 0 24 0 14.62 0 6.51 5.38 2.56 13.22l7.98 6.19C12.43 13.72 17.74 9.5 24 9.5z"/>
                    <path fill="#4285F4" d="M46.98 24.55c0-1.57-.15-3.09-.38-4.55H24v9.02h12.94c-.58 2.96-2.26 5.48-4.78 7.18l7.73 6c4.51-4.18 7.09-10.36 7.09-17.65z"/>
                    <path fill="#FBBC05" d="M10.53 28.59c-.48-1.45-.76-2.99-.76-4.59s.27-3.14.76-4.59l-7.98-6.19C.92 16.46 0 20.12 0 24c0 3.88.92 7.54 2.56 10.78l7.97-6.19z"/>
                    <path fill="#34A853" d="M24 48c6.48 0 11.93-2.13 15.89-5.81l-7.73-6c-2.15 1.45-4.92 2.3-8.16 2.3-6.26 0-11.57-4.22-13.47-9.91l-7.98 6.19C6.51 42.62 14.62 48 24 48z"/>
                </svg>
                Continue with Google
            </a>
        @endif

        @if ($socialProviders->contains('facebook'))
            <a href="{{ route('social.redirect', 'facebook') }}" class="btn-social btn-facebook">
                <svg viewBox="0 0 24 24" aria-hidden="true">
                    <path fill="#ffffff" d="M24 12.07C24 5.4 18.63 0 12 0S0 5.4 0 12.07C0 18.1 4.39 23.1 10.13 24v-8.44H7.08v-3.49h3.05V9.41c0-3.02 1.79-4.69 4.53-4.69 1.31 0 2.69.24 2.69.24v2.97h-1.51c-1.49 0-1.96.93-1.96 1.89v2.26h3.33l-.53 3.49h-2.8V24C19.61 23.1 24 18.1 24 12.07z"/>
                </svg>
                Continue with Facebook
            </a>
        @endif
    </div>
@endif
