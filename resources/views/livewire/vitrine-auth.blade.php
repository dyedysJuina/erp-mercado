<div style="width:100%;min-height:100vh;display:flex;align-items:center;justify-content:center;padding:1rem;background:var(--background);font-family:'Inter',system-ui,sans-serif;box-sizing:border-box;">
    <div style="width:100%;max-width:460px;background:var(--surface);border:1px solid var(--border);border-radius:2rem;padding:2rem 1.75rem;box-shadow:0 20px 50px color-mix(in srgb,var(--shadow)10%,transparent);">

        <div style="text-align:center;margin-bottom:1.5rem;">
            <div style="font-size:2rem;font-weight:700;color:var(--text);letter-spacing:-0.02em;">bem-vindo</div>
            <div style="color:var(--muted);font-size:0.9rem;margin-top:0.2rem;">acesse sua conta ou crie uma nova</div>
        </div>

        {{-- Tabs --}}
        <div style="display:flex;background:color-mix(in srgb,var(--text)6%,var(--surface));border-radius:60px;padding:0.25rem;margin-bottom:1.5rem;border:1px solid color-mix(in srgb,var(--border)50%,transparent);">
            <button wire:click="$set('aba', 'login')" style="flex:1;border:none;background:{{ $aba === 'login' ? 'var(--surface)' : 'transparent' }};padding:0.65rem 0.5rem;border-radius:40px;font-weight:600;font-size:0.9rem;color:{{ $aba === 'login' ? 'var(--text)' : 'var(--muted)' }};cursor:pointer;transition:all 0.2s;display:flex;align-items:center;justify-content:center;gap:0.4rem;box-shadow:{{ $aba === 'login' ? '0 2px 8px color-mix(in srgb,var(--shadow)5%,transparent)' : 'none' }};">
                <i class="fas fa-arrow-right-to-bracket" style="font-size:0.9rem;color:{{ $aba === 'login' ? 'var(--primary-500)' : 'inherit' }};"></i> Entrar
            </button>
            <button wire:click="$set('aba', 'register')" style="flex:1;border:none;background:{{ $aba === 'register' ? 'var(--surface)' : 'transparent' }};padding:0.65rem 0.5rem;border-radius:40px;font-weight:600;font-size:0.9rem;color:{{ $aba === 'register' ? 'var(--text)' : 'var(--muted)' }};cursor:pointer;transition:all 0.2s;display:flex;align-items:center;justify-content:center;gap:0.4rem;box-shadow:{{ $aba === 'register' ? '0 2px 8px color-mix(in srgb,var(--shadow)5%,transparent)' : 'none' }};">
                <i class="fas fa-user-plus" style="font-size:0.9rem;color:{{ $aba === 'register' ? 'var(--primary-500)' : 'inherit' }};"></i> Cadastrar
            </button>
        </div>

        {{-- LOGIN --}}
        @if ($aba === 'login')
            <form wire:submit="entrar">
                <div style="margin-bottom:1rem;">
                    <label style="display:block;font-size:0.75rem;font-weight:600;text-transform:uppercase;letter-spacing:0.03em;color:var(--muted);margin-bottom:0.35rem;">E-mail</label>
                    <div style="position:relative;">
                        <i class="fas fa-envelope" style="position:absolute;left:1rem;top:50%;transform:translateY(-50%);color:var(--muted);font-size:1rem;pointer-events:none;z-index:1;"></i>
                        <input wire:model.blur="login_email" type="email" required autocomplete="email" placeholder="seu@email.com"
                            style="width:100%;padding:0.8rem 1rem 0.8rem 2.8rem;font-size:0.95rem;border:1.5px solid var(--border);border-radius:60px;background:var(--surface);color:var(--text);outline:none;transition:all 0.2s;box-sizing:border-box;font-family:'Inter',system-ui,sans-serif;"
                            onfocus="this.style.borderColor='var(--primary-500)'" onblur="this.style.borderColor='var(--border)'">
                    </div>
                    @error('login_email') <p style="margin:0.2rem 0 0;font-size:0.78rem;font-weight:600;color:var(--danger);padding-left:0.5rem;">{{ $message }}</p> @enderror
                </div>

                <div style="margin-bottom:1rem;">
                    <label style="display:block;font-size:0.75rem;font-weight:600;text-transform:uppercase;letter-spacing:0.03em;color:var(--muted);margin-bottom:0.35rem;">Senha</label>
                    <div style="position:relative;" x-data="{ show: false }">
                        <i class="fas fa-lock" style="position:absolute;left:1rem;top:50%;transform:translateY(-50%);color:var(--muted);font-size:1rem;pointer-events:none;z-index:1;"></i>
                        <input wire:model.blur="login_password" :type="show ? 'text' : 'password'" required autocomplete="current-password" placeholder="sua senha"
                            style="width:100%;padding:0.8rem 2.8rem 0.8rem 2.8rem;font-size:0.95rem;border:1.5px solid var(--border);border-radius:60px;background:var(--surface);color:var(--text);outline:none;transition:all 0.2s;box-sizing:border-box;font-family:'Inter',system-ui,sans-serif;"
                            onfocus="this.style.borderColor='var(--primary-500)'" onblur="this.style.borderColor='var(--border)'">
                        <i class="fas" :class="show ? 'fa-eye-slash' : 'fa-eye'" x-on:click="show = !show" style="position:absolute;right:1.1rem;top:50%;transform:translateY(-50%);color:var(--muted);cursor:pointer;font-size:1rem;z-index:1;"></i>
                    </div>
                </div>

                <div style="display:flex;align-items:center;justify-content:space-between;margin:0.5rem 0 1.25rem;font-size:0.85rem;">
                    <label style="display:flex;align-items:center;gap:0.4rem;cursor:pointer;color:var(--text);">
                        <input type="checkbox" style="accent-color:var(--primary-500);width:15px;height:15px;"> Lembrar-me
                    </label>
                    <a href="#" style="font-size:0.82rem;font-weight:500;color:var(--primary-500);text-decoration:none;">Esqueceu a senha?</a>
                </div>

                <button type="submit" wire:loading.attr="disabled" wire:target="entrar"
                    style="width:100%;padding:0.8rem;background:var(--primary-600);border:none;border-radius:60px;font-weight:600;font-size:0.95rem;color:var(--on-primary);display:flex;align-items:center;justify-content:center;gap:0.5rem;cursor:pointer;transition:all 0.2s;font-family:'Inter',system-ui,sans-serif;box-sizing:border-box;"
                    onmouseover="this.style.background='var(--primary-700)'" onmouseout="this.style.background='var(--primary-600)'">
                    <span wire:loading.remove wire:target="entrar"><i class="fas fa-arrow-right-to-bracket"></i> Entrar</span>
                    <span wire:loading wire:target="entrar">Entrando...</span>
                </button>
            </form>

            <div style="text-align:center;margin-top:1.25rem;font-size:0.85rem;color:var(--muted);">
                Nao tem conta? <a href="#" wire:click.prevent="$set('aba', 'register')" style="color:var(--primary-500);font-weight:600;text-decoration:none;">Cadastre-se</a>
            </div>
        @endif

        {{-- REGISTER --}}
        @if ($aba === 'register')
            <form wire:submit="registrar">
                <div style="margin-bottom:1rem;">
                    <label style="display:block;font-size:0.75rem;font-weight:600;text-transform:uppercase;letter-spacing:0.03em;color:var(--muted);margin-bottom:0.35rem;">Nome completo</label>
                    <div style="position:relative;">
                        <i class="fas fa-user" style="position:absolute;left:1rem;top:50%;transform:translateY(-50%);color:var(--muted);font-size:1rem;pointer-events:none;z-index:1;"></i>
                        <input wire:model.blur="reg_nome" type="text" required autocomplete="name" placeholder="Ana Silva"
                            style="width:100%;padding:0.8rem 1rem 0.8rem 2.8rem;font-size:0.95rem;border:1.5px solid var(--border);border-radius:60px;background:var(--surface);color:var(--text);outline:none;transition:all 0.2s;box-sizing:border-box;font-family:'Inter',system-ui,sans-serif;"
                            onfocus="this.style.borderColor='var(--primary-500)'" onblur="this.style.borderColor='var(--border)'">
                    </div>
                    @error('reg_nome') <p style="margin:0.2rem 0 0;font-size:0.78rem;font-weight:600;color:var(--danger);padding-left:0.5rem;">{{ $message }}</p> @enderror
                </div>

                <div style="margin-bottom:1rem;">
                    <label style="display:block;font-size:0.75rem;font-weight:600;text-transform:uppercase;letter-spacing:0.03em;color:var(--muted);margin-bottom:0.35rem;">E-mail</label>
                    <div style="position:relative;">
                        <i class="fas fa-envelope" style="position:absolute;left:1rem;top:50%;transform:translateY(-50%);color:var(--muted);font-size:1rem;pointer-events:none;z-index:1;"></i>
                        <input wire:model.blur="reg_email" type="email" required autocomplete="email" placeholder="ana@email.com"
                            style="width:100%;padding:0.8rem 1rem 0.8rem 2.8rem;font-size:0.95rem;border:1.5px solid var(--border);border-radius:60px;background:var(--surface);color:var(--text);outline:none;transition:all 0.2s;box-sizing:border-box;font-family:'Inter',system-ui,sans-serif;"
                            onfocus="this.style.borderColor='var(--primary-500)'" onblur="this.style.borderColor='var(--border)'">
                    </div>
                    @error('reg_email') <p style="margin:0.2rem 0 0;font-size:0.78rem;font-weight:600;color:var(--danger);padding-left:0.5rem;">{{ $message }}</p> @enderror
                </div>

                <div style="margin-bottom:1rem;">
                    <label style="display:block;font-size:0.75rem;font-weight:600;text-transform:uppercase;letter-spacing:0.03em;color:var(--muted);margin-bottom:0.35rem;">WhatsApp</label>
                    <div style="position:relative;">
                        <i class="fas fa-phone" style="position:absolute;left:1rem;top:50%;transform:translateY(-50%);color:var(--muted);font-size:1rem;pointer-events:none;z-index:1;"></i>
                        <input wire:model.blur="reg_whatsapp" type="tel" required placeholder="(66) 99999-9999"
                            style="width:100%;padding:0.8rem 1rem 0.8rem 2.8rem;font-size:0.95rem;border:1.5px solid var(--border);border-radius:60px;background:var(--surface);color:var(--text);outline:none;transition:all 0.2s;box-sizing:border-box;font-family:'Inter',system-ui,sans-serif;"
                            onfocus="this.style.borderColor='var(--primary-500)'" onblur="this.style.borderColor='var(--border)'"
                            x-data
                            x-init="$el.addEventListener('input', function(e) {
                                let v = e.target.value.replace(/\D/g, '').substring(0, 11);
                                let masked = '';
                                if (v.length <= 2) { masked = v; }
                                else if (v.length <= 6) { masked = '(' + v.substring(0,2) + ') ' + v.substring(2); }
                                else if (v.length <= 10) { masked = '(' + v.substring(0,2) + ') ' + v.substring(2,7) + '-' + v.substring(7); }
                                else { masked = '(' + v.substring(0,2) + ') ' + v.substring(2,7) + '-' + v.substring(7,11); }
                                if (e.target.value !== masked) setTimeout(() => { e.target.value = masked; }, 0);
                            })">
                    </div>
                    @error('reg_whatsapp') <p style="margin:0.2rem 0 0;font-size:0.78rem;font-weight:600;color:var(--danger);padding-left:0.5rem;">{{ $message }}</p> @enderror
                </div>

                <div style="margin-bottom:0.8rem;">
                    <label style="display:block;font-size:0.75rem;font-weight:600;text-transform:uppercase;letter-spacing:0.03em;color:var(--muted);margin-bottom:0.35rem;">Senha</label>
                    <div style="position:relative;" x-data="{ show: false }">
                        <i class="fas fa-lock" style="position:absolute;left:1rem;top:50%;transform:translateY(-50%);color:var(--muted);font-size:1rem;pointer-events:none;z-index:1;"></i>
                        <input wire:model.blur="reg_password" :type="show ? 'text' : 'password'" required autocomplete="new-password" placeholder="minimo 6 caracteres"
                            style="width:100%;padding:0.8rem 2.8rem 0.8rem 2.8rem;font-size:0.95rem;border:1.5px solid var(--border);border-radius:60px;background:var(--surface);color:var(--text);outline:none;transition:all 0.2s;box-sizing:border-box;font-family:'Inter',system-ui,sans-serif;"
                            onfocus="this.style.borderColor='var(--primary-500)'" onblur="this.style.borderColor='var(--border)'">
                        <i class="fas" :class="show ? 'fa-eye-slash' : 'fa-eye'" x-on:click="show = !show" style="position:absolute;right:1.1rem;top:50%;transform:translateY(-50%);color:var(--muted);cursor:pointer;font-size:1rem;z-index:1;"></i>
                    </div>
                    @error('reg_password') <p style="margin:0.2rem 0 0;font-size:0.78rem;font-weight:600;color:var(--danger);padding-left:0.5rem;">{{ $message }}</p> @enderror
                </div>

                <div style="margin-bottom:1.25rem;">
                    <label style="display:block;font-size:0.75rem;font-weight:600;text-transform:uppercase;letter-spacing:0.03em;color:var(--muted);margin-bottom:0.35rem;">Confirmar senha</label>
                    <div style="position:relative;" x-data="{ show: false }">
                        <i class="fas fa-check-circle" style="position:absolute;left:1rem;top:50%;transform:translateY(-50%);color:var(--muted);font-size:1rem;pointer-events:none;z-index:1;"></i>
                        <input wire:model.blur="reg_password_confirmation" :type="show ? 'text' : 'password'" required autocomplete="new-password" placeholder="confirme sua senha"
                            style="width:100%;padding:0.8rem 2.8rem 0.8rem 2.8rem;font-size:0.95rem;border:1.5px solid var(--border);border-radius:60px;background:var(--surface);color:var(--text);outline:none;transition:all 0.2s;box-sizing:border-box;font-family:'Inter',system-ui,sans-serif;"
                            onfocus="this.style.borderColor='var(--primary-500)'" onblur="this.style.borderColor='var(--border)'">
                        <i class="fas" :class="show ? 'fa-eye-slash' : 'fa-eye'" x-on:click="show = !show" style="position:absolute;right:1.1rem;top:50%;transform:translateY(-50%);color:var(--muted);cursor:pointer;font-size:1rem;z-index:1;"></i>
                    </div>
                </div>

                <button type="submit" wire:loading.attr="disabled" wire:target="registrar"
                    style="width:100%;padding:0.8rem;background:var(--primary-600);border:none;border-radius:60px;font-weight:600;font-size:0.95rem;color:var(--on-primary);display:flex;align-items:center;justify-content:center;gap:0.5rem;cursor:pointer;transition:all 0.2s;font-family:'Inter',system-ui,sans-serif;box-sizing:border-box;"
                    onmouseover="this.style.background='var(--primary-700)'" onmouseout="this.style.background='var(--primary-600)'">
                    <span wire:loading.remove wire:target="registrar"><i class="fas fa-user-plus"></i> Criar conta</span>
                    <span wire:loading wire:target="registrar">Criando...</span>
                </button>
            </form>

            <div style="text-align:center;margin-top:1.25rem;font-size:0.85rem;color:var(--muted);">
                Ja tem conta? <a href="#" wire:click.prevent="$set('aba', 'login')" style="color:var(--primary-500);font-weight:600;text-decoration:none;">Faca login</a>
            </div>
        @endif
    </div>
</div>
