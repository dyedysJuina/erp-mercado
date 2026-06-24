<div style="min-height:100vh;display:flex;align-items:center;justify-content:center;padding:1.5rem;background:var(--background);font-family:'Inter',system-ui,sans-serif;">
    <div style="width:100%;max-width:400px;background:var(--surface);border:1px solid var(--border);border-radius:20px;padding:2.5rem;box-shadow:0 10px 25px -5px color-mix(in srgb,var(--shadow)10%,transparent);">
        <div style="text-align:center;margin-bottom:2rem;">
            <span style="display:inline-flex;align-items:center;justify-content:center;width:48px;height:48px;border-radius:12px;background:var(--primary-600);color:var(--on-primary);font-weight:900;font-size:1.2rem;margin-bottom:0.75rem;">E</span>
            <h1 style="font-size:1.5rem;font-weight:800;color:var(--text);margin:0 0 0.25rem;letter-spacing:-0.03em;">Entrar</h1>
            <p style="color:var(--muted);font-size:0.9rem;margin:0;">Acesse sua conta para comprar</p>
        </div>

        <form wire:submit="entrar">
            <div style="margin-bottom:1rem;">
                <label for="email" style="display:block;font-size:0.75rem;font-weight:700;color:var(--muted);margin-bottom:0.35rem;text-transform:uppercase;">E-mail</label>
                <input wire:model.blur="email" type="email" id="email" required autocomplete="email" placeholder="seu@email.com"
                    style="width:100%;background:var(--surface);border:1.5px solid var(--border);padding:0.75rem 1rem;border-radius:8px;font-size:0.9rem;color:var(--text);outline:none;transition:all 0.2s;box-sizing:border-box;"
                    onfocus="this.style.borderColor='var(--primary-500)'" onblur="this.style.borderColor='var(--border)'">
                @error('email') <p style="margin:0.2rem 0 0;font-size:0.78rem;font-weight:600;color:var(--danger);">{{ $message }}</p> @enderror
            </div>

            <div style="margin-bottom:1.5rem;">
                <label for="password" style="display:block;font-size:0.75rem;font-weight:700;color:var(--muted);margin-bottom:0.35rem;text-transform:uppercase;">Senha</label>
                <input wire:model.blur="password" type="password" id="password" required autocomplete="current-password" placeholder="Sua senha"
                    style="width:100%;background:var(--surface);border:1.5px solid var(--border);padding:0.75rem 1rem;border-radius:8px;font-size:0.9rem;color:var(--text);outline:none;transition:all 0.2s;box-sizing:border-box;"
                    onfocus="this.style.borderColor='var(--primary-500)'" onblur="this.style.borderColor='var(--border)'">
            </div>

            <button type="submit" wire:loading.attr="disabled" wire:target="entrar"
                style="width:100%;background:var(--primary-600);color:var(--on-primary);border:none;padding:0.8rem;border-radius:8px;font-size:0.9rem;font-weight:700;cursor:pointer;transition:all 0.2s;box-sizing:border-box;margin-bottom:1rem;"
                onmouseover="this.style.background='var(--primary-700)'" onmouseout="this.style.background='var(--primary-600)'">
                <span wire:loading.remove wire:target="entrar">Entrar</span>
                <span wire:loading wire:target="entrar">Entrando...</span>
            </button>
        </form>

        <div style="text-align:center;font-size:0.85rem;color:var(--muted);">
            Nao tem conta? <a href="{{ route('vitrine.cadastro') }}" wire:navigate style="color:var(--primary-500);text-decoration:none;font-weight:600;">Criar conta</a>
        </div>
    </div>
</div>
