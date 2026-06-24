<div style="display:flex;width:100%;min-height:100vh;">
    {{-- Lado Esquerdo: Brand --}}
    <div style="flex:1;background:linear-gradient(135deg, color-mix(in srgb, var(--primary-700) 75%, #000), color-mix(in srgb, var(--primary-800) 75%, #000));display:flex;flex-direction:column;justify-content:space-between;padding:3.5rem;position:relative;overflow:hidden;">
        <div style="position:absolute;top:-20%;right:-15%;width:550px;height:550px;background:color-mix(in srgb,var(--primary-500)10%,transparent);border-radius:50%;filter:blur(100px);pointer-events:none;"></div>
        <div style="position:absolute;bottom:-25%;left:-15%;width:450px;height:450px;background:color-mix(in srgb,var(--primary-400)6%,transparent);border-radius:50%;filter:blur(90px);pointer-events:none;"></div>

        <div style="z-index:2;">
            <div style="display:flex;align-items:center;gap:0.75rem;">
                <span style="display:grid;place-items:center;width:42px;height:42px;border-radius:10px;background:var(--primary-600);color:#fff;font-weight:900;font-size:1rem;">E</span>
                <div>
                    <span style="font-size:1.1rem;font-weight:800;color:#fff;letter-spacing:-0.02em;">ERP Mercado</span>
                    <span style="display:block;font-size:0.7rem;color:color-mix(in srgb,#fff 50%,transparent);font-weight:500;">Sistema de Gestao</span>
                </div>
            </div>
        </div>

        <div style="z-index:2;max-width:440px;">
            <h2 style="color:#fff;font-size:2rem;font-weight:800;line-height:1.25;letter-spacing:-0.03em;margin:0 0 1rem;">Operacao inteligente,<br>gestao descomplicada.</h2>
            <p style="color:color-mix(in srgb,#fff 55%,transparent);font-size:0.95rem;line-height:1.7;margin:0;">Acesse sua plataforma integrada de frente de caixa, inventarios de gondolas em tempo real e inteligencia de mercado de forma centralizada.</p>
        </div>

        <div style="color:color-mix(in srgb,#fff 35%,transparent);font-size:0.78rem;z-index:2;">
            &copy; {{ date('Y') }} ERP Mercado System.
        </div>
    </div>

    {{-- Lado Direito: Formulario --}}
    <div style="width:500px;background:var(--surface);display:flex;flex-direction:column;justify-content:center;padding:3.5rem;">
        <div style="margin-bottom:2rem;">
            <h3 style="font-size:1.5rem;font-weight:800;color:var(--text);letter-spacing:-0.03em;margin:0 0 0.25rem;">Acessar o sistema</h3>
            <p style="color:var(--muted);font-size:0.9rem;margin:0;">Insira suas credenciais administrativas abaixo</p>
        </div>

        <form wire:submit="entrar">
            <div style="margin-bottom:1.25rem;">
                <label for="email" style="display:block;font-size:0.75rem;font-weight:700;color:var(--muted);margin-bottom:0.35rem;text-transform:uppercase;letter-spacing:0.03em;">E-mail</label>
                <input wire:model.blur="email" type="email" id="email" autocomplete="email" required placeholder="seu@email.com"
                    style="width:100%;background:var(--surface);border:1.5px solid var(--border);padding:0.75rem 1rem;border-radius:8px;font-size:0.9rem;color:var(--text);outline:none;transition:all 0.2s;box-sizing:border-box;"
                    onfocus="this.style.borderColor='var(--primary-500)'"
                    onblur="this.style.borderColor='var(--border)'">
                @error('email') <p style="margin:0.2rem 0 0;font-size:0.78rem;font-weight:600;color:var(--danger);">{{ $message }}</p> @enderror
            </div>

            <div style="margin-bottom:1.25rem;">
                <label for="password" style="display:block;font-size:0.75rem;font-weight:700;color:var(--muted);margin-bottom:0.35rem;text-transform:uppercase;letter-spacing:0.03em;">Senha</label>
                <div style="position:relative;" x-data="{ show: false }">
                    <input wire:model.blur="password" :type="show ? 'text' : 'password'" id="password" autocomplete="current-password" required placeholder="Sua senha"
                        style="width:100%;background:var(--surface);border:1.5px solid var(--border);padding:0.75rem 2.5rem 0.75rem 1rem;border-radius:8px;font-size:0.9rem;color:var(--text);outline:none;transition:all 0.2s;box-sizing:border-box;"
                        onfocus="this.style.borderColor='var(--primary-500)'"
                        onblur="this.style.borderColor='var(--border)'">
                    <i class="fa-solid" x-on:click="show = !show" :class="show ? 'fa-eye-slash' : 'fa-eye'" style="position:absolute;right:0.8rem;top:50%;transform:translateY(-50%);color:var(--muted);cursor:pointer;font-size:0.9rem;"></i>
                </div>
            </div>

            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1.5rem;font-size:0.85rem;">
                <label style="display:flex;align-items:center;gap:0.4rem;cursor:pointer;color:var(--muted);font-weight:500;">
                    <input wire:model="lembrar" type="checkbox" style="accent-color:var(--primary-500);width:15px;height:15px;cursor:pointer;margin:0;">
                    <span>Continuar conectado</span>
                </label>
                <a href="#" style="color:var(--primary-500);text-decoration:none;font-weight:600;font-size:0.85rem;">Esqueceu a senha?</a>
            </div>

            <button type="submit" wire:loading.attr="disabled" wire:target="entrar"
                style="width:100%;background:var(--primary-600);color:#fff;border:none;padding:0.8rem;border-radius:8px;font-size:0.9rem;font-weight:700;cursor:pointer;transition:all 0.2s;box-sizing:border-box;"
                onmouseover="this.style.background='var(--primary-700)'"
                onmouseout="this.style.background='var(--primary-600)'">
                <span>Entrar no Painel</span>
                <span wire:loading wire:target="entrar" style="display:none;align-items:center;gap:0.5rem;">
                    <svg style="width:16px;height:16px;animation:spin 0.8s linear infinite;" viewBox="0 0 24 24" fill="none">
                        <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" style="opacity:0.25;"/>
                        <path fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" style="opacity:0.75;"/>
                    </svg>
                    Autenticando...
                </span>
            </button>
            <style>@keyframes spin { to { transform: rotate(360deg); } }</style>
        </form>
    </div>
</div>
