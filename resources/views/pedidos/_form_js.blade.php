@once
  @push('scripts')
    <script>
      window.pedidoForm = function ({ products, initialItems, initialCliente }) {
        return {
          // aseguramos que los ids en products sean strings
          products: (Array.isArray(products) ? products : []).map(p => ({
            id: String(p.id),
            nombre: p.nombre,
            precio: Number(p.precio) || 0,
            _ghost: !!p._ghost,
          })),
          items: [],
          id_cliente: initialCliente ?? '',

          init() {
            // id_producto como string
            this.items = (initialItems || []).map(i => ({
              id_producto: i.id_producto !== '' && i.id_producto != null ? String(i.id_producto) : '',
              cantidad: Number(i.cantidad) || 1,
              precio_unitario: Number(i.precio_unitario) || 0,
            }));

            this.ensureMissingProductsPresent();

            if (!this.items.length) this.addItem();
            this.id_cliente = String(this.id_cliente ?? '');
          },

          ensureMissingProductsPresent() {
            this.items.forEach(it => {
              const id = String(it.id_producto || '');
              if (!id) return;
              const exists = this.products.some(p => String(p.id) === id);
              if (!exists) {
                this.products.push({
                  id,
                  nombre: `Producto #${id} (inactivo)`,
                  precio: Number(it.precio_unitario) || 0,
                  _ghost: true,
                });
              }
            });
            this.products.sort((a,b) => String(a.nombre).localeCompare(String(b.nombre)));
          },

          addItem() {
            this.items.push({ id_producto: '', cantidad: 1, precio_unitario: 0 });
          },

          removeItem(i) {
            this.items.splice(i, 1);
            if (!this.items.length) this.addItem();
          },

          onProductChange(i) {
            const it = this.items[i];
            const p  = this.products.find(pp => String(pp.id) === String(it.id_producto));
            if (p && (!it.precio_unitario || it.precio_unitario === 0)) {
              it.precio_unitario = Number(p.precio) || 0;
            }
          },

          lineSubtotal(i) {
            const it = this.items[i];
            return (Number(it.cantidad) || 0) * (Number(it.precio_unitario) || 0);
          },

          total() {
            return this.items.reduce((t, _, i) => t + this.lineSubtotal(i), 0);
          },

          formatMoney(v) {
            return (Number(v) || 0).toFixed(2);
          },
        }
      }
    </script>
  @endpush
@endonce
