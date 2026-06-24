import './bootstrap';

const applyTheme = (detail, applyTokens = false) => {
    if (!detail?.theme) return;

    document.documentElement.dataset.theme = detail.theme;

    if (applyTokens && detail.tokens) {
        Object.entries(detail.tokens).forEach(([key, value]) => {
            if (/^#[0-9a-fA-F]{6}([0-9a-fA-F]{2})?$/.test(value)) {
                document.documentElement.style.setProperty(`--${key.replaceAll('_', '-')}`, value);
            }
        });
    } else {
        Array.from(document.documentElement.style).forEach((property) => {
            if (property.startsWith('--')) {
                document.documentElement.style.removeProperty(property);
            }
        });
    }
};

window.addEventListener('theme-changed', (event) => applyTheme(event.detail));
window.addEventListener('theme-preview', (event) => applyTheme(event.detail, true));
window.addEventListener('theme-updated', (event) => applyTheme(event.detail, true));

const decimal = (value) => {
    const number = Number(value);

    return Number.isFinite(number) ? number : 0;
};

const formatMoney = (value) => decimal(value).toLocaleString('pt-BR', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
});

const formatPercentage = (value) => decimal(value).toLocaleString('pt-BR', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 4,
});

document.addEventListener('alpine:init', () => {
    Alpine.data('priceTableEditor', () => ({
        saving: false,

        async save($wire) {
            const rows = Array.from(document.querySelectorAll('#preco-tbody tr'))
                .map((row) => Alpine.$data(row))
                .filter((row) => row?.id)
                .map((row) => ({
                    id: row.id,
                    c: decimal(row.custo).toFixed(4),
                    m: decimal(row.margem).toFixed(4),
                    v: decimal(row.venda).toFixed(2),
                    a: decimal(row.atacado).toFixed(2),
                }));

            if (rows.length === 0 || this.saving) return;

            this.saving = true;

            try {
                await $wire.salvarTodos(rows);
            } finally {
                this.saving = false;
            }
        },
    }));

    Alpine.data('linhaPreco', (initial) => ({
        id: initial.id,
        custo: decimal(initial.custo),
        margem: decimal(initial.margem),
        venda: decimal(initial.venda),
        atacado: decimal(initial.atacado),
        custoDisplay: formatMoney(initial.custo),
        margemDisplay: formatPercentage(initial.margem),
        vendaDisplay: formatMoney(initial.venda),
        atacadoDisplay: formatMoney(initial.atacado),

        moneyInput(field, event) {
            const digits = event.target.value.replace(/\D/g, '').slice(0, 12);
            const value = digits === '' ? 0 : Number(digits) / 100;

            this[field] = value;
            this[`${field}Display`] = formatMoney(value);

            if (field === 'custo') this.recalculateSale();
            if (field === 'venda') this.recalculateMargin();
        },

        percentageInput(event) {
            let value = event.target.value.replace(/[^\d,.-]/g, '').replace('.', ',');
            const negative = value.startsWith('-');
            value = value.replace(/-/g, '');

            const [integer = '', ...fractions] = value.split(',');
            const fraction = fractions.join('').slice(0, 4);
            this.margemDisplay = `${negative ? '-' : ''}${integer}${value.includes(',') ? `,${fraction}` : ''}`;
            this.margem = decimal(this.margemDisplay.replace(',', '.'));
            this.recalculateSale();
        },

        percentageBlur() {
            this.margemDisplay = formatPercentage(this.margem);
        },

        recalculateSale() {
            this.venda = Math.max(0, this.custo * (1 + this.margem / 100));
            this.vendaDisplay = formatMoney(this.venda);
        },

        recalculateMargin() {
            this.margem = this.custo > 0 ? ((this.venda / this.custo) - 1) * 100 : 0;
            this.margemDisplay = formatPercentage(this.margem);
        },
    }));
});
