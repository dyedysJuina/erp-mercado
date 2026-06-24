<x-layouts.app title="Componentes · ERP Mercado">
    <div class="space-y-8">
        {{-- Título --}}
        <div>
            <x-breadcrumb :items="['Componentes' => '#']" />
            <h1 class="mt-2 text-2xl font-extrabold tracking-tight" style="color: var(--text);">Design System</h1>
            <p class="mt-1 text-sm" style="color: var(--muted);">Componentes reutilizáveis do ERP Mercado</p>
        </div>

        {{-- Botões --}}
        <section>
            <h2 class="mb-3 text-lg font-extrabold tracking-tight" style="color: var(--text);">Botões</h2>
            <div class="flex flex-wrap gap-3">
                <x-buttons variant="primary">Primário</x-buttons>
                <x-buttons variant="secondary">Secundário</x-buttons>
                <x-buttons variant="danger">Perigo</x-buttons>
                <x-buttons variant="primary" loading>Carregando</x-buttons>
                <x-buttons variant="primary" disabled>Desabilitado</x-buttons>
            </div>
        </section>

        {{-- Formulários --}}
        <section>
            <h2 class="mb-3 text-lg font-extrabold tracking-tight" style="color: var(--text);">Formulários</h2>
            <div class="grid gap-4 sm:grid-cols-2">
                <x-forms.input name="demo-nome" label="Nome" placeholder="Seu nome" />
                <x-forms.input name="demo-email" label="E-mail" type="email" placeholder="seu@email.com" />
                <x-forms.input name="demo-preco" label="Preço" type="text" placeholder="R$ 0,00" />
                <x-forms.input name="demo-data" label="Data" type="date" />
                <x-forms.textarea name="demo-descricao" label="Descrição" placeholder="Descreva..." rows="3" />
                <x-forms.select name="demo-select" label="Categoria" :options="['' => 'Selecione...', '1' => 'Limpeza', '2' => 'Alimentos', '3' => 'Bebidas']" />
            </div>
            <div class="mt-4 space-y-2">
                <x-forms.checkbox name="demo-check" label="Ativar produto" />
                <x-forms.radio name="demo-radio" label="Sim" value="1" />
                <x-forms.radio name="demo-radio" label="Não" value="0" checked />
            </div>
            <x-forms.help>Este é um texto de ajuda para o formulário.</x-forms.help>
        </section>

        {{-- Card --}}
        <section>
            <h2 class="mb-3 text-lg font-extrabold tracking-tight" style="color: var(--text);">Card</h2>
            <div class="grid gap-4 sm:grid-cols-3">
                <x-card>
                    <h3 class="font-bold" style="color: var(--text);">Card simples</h3>
                    <p class="mt-1 text-sm" style="color: var(--muted);">Conteúdo do card com padding padrão.</p>
                </x-card>
                <x-card>
                    <h3 class="font-bold" style="color: var(--text);">Com informações</h3>
                    <p class="mt-1 text-sm" style="color: var(--muted);">Apenas texto como exemplo de layout.</p>
                </x-card>
                <x-card :padding="false">
                    <div class="px-5 py-4">
                        <h3 class="font-bold" style="color: var(--text);">Sem padding</h3>
                        <p class="mt-1 text-sm" style="color: var(--muted);">Card sem padding para conteúdo customizado.</p>
                    </div>
                </x-card>
            </div>
        </section>

        {{-- Badges --}}
        <section>
            <h2 class="mb-3 text-lg font-extrabold tracking-tight" style="color: var(--text);">Badges</h2>
            <div class="flex flex-wrap gap-3">
                <x-badge variant="success">Ativo</x-badge>
                <x-badge variant="warning">Pendente</x-badge>
                <x-badge variant="danger">Inativo</x-badge>
            </div>
        </section>

        {{-- Alertas --}}
        <section>
            <h2 class="mb-3 text-lg font-extrabold tracking-tight" style="color: var(--text);">Alertas</h2>
            <div class="space-y-3">
                <x-alert variant="success">Registro salvo com sucesso!</x-alert>
                <x-alert variant="warning">Atenção: esta ação não pode ser desfeita.</x-alert>
                <x-alert variant="danger">Erro ao salvar. Verifique os campos.</x-alert>
                <x-alert variant="info" dismissible>Alerta informativo com botão de fechar.</x-alert>
            </div>
        </section>

        {{-- Tabela --}}
        <section>
            <h2 class="mb-3 text-lg font-extrabold tracking-tight" style="color: var(--text);">Tabela</h2>
            <x-table
                :headers="['Produto', 'Categoria', 'Preço', 'Status']"
                :rows="[
                    ['Arroz Tipo 1', 'Alimentos', 'R$ 25,90', '<span class=\'badge-success\'>Ativo</span>'],
                    ['Sabão em Pó', 'Limpeza', 'R$ 18,50', '<span class=\'badge-success\'>Ativo</span>'],
                    ['Refrigerante Cola', 'Bebidas', 'R$ 7,90', '<span class=\'badge-warning\'>Pendente</span>'],
                ]"
            />
        </section>

        {{-- Skeleton --}}
        <section>
            <h2 class="mb-3 text-lg font-extrabold tracking-tight" style="color: var(--text);">Skeleton</h2>
            <div class="space-y-3">
                <x-skeleton class="h-8 w-48" />
                <x-skeleton class="h-4 w-full" />
                <x-skeleton class="h-4 w-3/4" />
                <x-skeleton :count="3" class="h-4 w-full" />
            </div>
        </section>

        {{-- Empty State --}}
        <section>
            <h2 class="mb-3 text-lg font-extrabold tracking-tight" style="color: var(--text);">Estado vazio</h2>
            <x-empty-state title="Nenhuma categoria encontrada" description="Crie sua primeira categoria para começar." />
        </section>
    </div>
</x-layouts.app>
