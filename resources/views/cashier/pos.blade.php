@extends('layouts.app')

@section('title', 'POS Kasir')

@section('content')
<style>
    #invoiceModal .modal-dialog { max-width: 760px; }
    #invoiceModal .modal-content {
        overflow: hidden;
        border: 0;
        border-radius: 1rem;
        box-shadow: 0 1.5rem 4rem rgba(15, 23, 42, .22);
    }
    #invoiceModal .modal-header {
        padding: 1.25rem 1.5rem;
        background: linear-gradient(135deg, #f0fdf4, #fff 70%);
        border-bottom: 1px solid #e8eee9;
    }
    #invoiceModal .invoice-success-icon {
        display: grid;
        width: 2.5rem;
        height: 2.5rem;
        flex: 0 0 2.5rem;
        place-items: center;
        border-radius: 50%;
        color: #15803d;
        background: #dcfce7;
        font-size: 1.25rem;
        font-weight: 700;
    }
    #invoiceModal .modal-title { color: #17211b; font-weight: 700; }
    #invoiceModal .invoice-number {
        display: inline-block;
        margin-top: .2rem;
        color: #64748b;
        font-size: .82rem;
        letter-spacing: .025em;
    }
    #invoiceModal .modal-body { padding: 1.5rem; }
    #invoiceModal .invoice-brand { color: #17211b; font-size: 1.4rem; font-weight: 750; letter-spacing: -.035em; }
    #invoiceModal .invoice-document-label {
        display: inline-block;
        margin-top: .35rem;
        padding: .3rem .65rem;
        border-radius: 999px;
        color: #475569;
        background: #f1f5f9;
        font-size: .7rem;
        font-weight: 700;
        letter-spacing: .1em;
    }
    #invoiceModal .invoice-store-details { color: #64748b; font-size: .84rem; }
    #invoiceModal .invoice-meta {
        margin: 1.25rem 0;
        padding: .9rem 1rem;
        border: 1px solid #e8edf2;
        border-radius: .75rem;
        background: #f8fafc;
    }
    #invoiceModal .invoice-meta-item { color: #64748b; font-size: .78rem; }
    #invoiceModal .invoice-meta-item strong { display: block; margin-top: .2rem; color: #1e293b; font-size: .92rem; font-weight: 600; }
    #invoiceModal .invoice-table { margin-bottom: 1.25rem; }
    #invoiceModal .invoice-table thead th {
        padding-top: .7rem;
        padding-bottom: .7rem;
        border-bottom: 1px solid #dbe3eb;
        color: #64748b;
        font-size: .72rem;
        font-weight: 700;
        letter-spacing: .07em;
        text-transform: uppercase;
    }
    #invoiceModal .invoice-table tbody td { padding-top: .75rem; padding-bottom: .75rem; border-color: #edf1f5; color: #334155; }
    #invoiceModal .invoice-table tbody td:first-child { color: #17211b; font-weight: 600; }
    #invoiceModal .invoice-totals { max-width: 380px; margin-left: auto; }
    #invoiceModal .invoice-total-row { padding: .22rem 0; color: #64748b; font-size: .9rem; }
    #invoiceModal .invoice-grand-total {
        margin: .55rem 0;
        padding: .8rem 0;
        border-top: 1px dashed #cbd5e1;
        border-bottom: 1px dashed #cbd5e1;
        color: #17211b;
        font-size: 1.1rem;
    }
    #invoiceModal .invoice-grand-total strong:last-child { color: #15803d; font-size: 1.35rem; }
    #invoiceModal .invoice-thank-you { margin-top: 1.25rem; color: #64748b; font-size: .85rem; }
    #invoiceModal .modal-footer {
        display: grid;
        grid-template-columns: 1fr;
        gap: .75rem;
        padding: 1rem 1.5rem 1.25rem;
        border-top: 1px solid #e8edf2;
        background: #fbfcfd;
    }
    #invoiceModal .invoice-action-row { display: flex; flex-wrap: wrap; gap: .6rem; }
    #invoiceModal .invoice-action-row .btn { min-height: 42px; }
    #invoiceModal .invoice-printer-panel {
        display: grid;
        grid-template-columns: minmax(0, 1fr) auto;
        gap: .65rem;
        align-items: end;
        padding: .85rem;
        border: 1px solid #e8edf2;
        border-radius: .75rem;
        background: #fff;
    }
    #invoiceModal .invoice-printer-panel label { margin-bottom: .35rem; color: #475569; font-size: .78rem; font-weight: 600; }
    #invoiceModal .invoice-help { margin: 0; color: #64748b; font-size: .78rem; line-height: 1.5; }
    #invoiceModal #thermalPrintMessage:not(:empty) { padding: .55rem .7rem; border-radius: .5rem; background: #f1f5f9; }
    #invoiceModal .invoice-done-button { justify-self: end; min-width: 190px; }
    @media (max-width: 575.98px) {
        #invoiceModal .modal-dialog { margin: .5rem; }
        #invoiceModal .modal-header, #invoiceModal .modal-body { padding: 1rem; }
        #invoiceModal .modal-footer { padding: .85rem 1rem 1rem; }
        #invoiceModal .invoice-printer-panel { grid-template-columns: 1fr; }
        #invoiceModal .invoice-printer-panel .btn { width: 100%; }
        #invoiceModal .invoice-done-button { justify-self: stretch; width: 100%; }
        #invoiceModal .invoice-meta { margin: .9rem 0; }
    }
    @media print {
        body.printing-invoice * { visibility: hidden !important; }
        body.printing-invoice #invoicePrintArea,
        body.printing-invoice #invoicePrintArea * { visibility: visible !important; }
        body.printing-invoice #invoicePrintArea {
            position: absolute;
            left: 0;
            top: 0;
            width: 76mm;
            padding: 3mm;
            color: #000;
            background: #fff;
        }
        body.printing-invoice #invoicePrintArea .invoice-meta,
        body.printing-invoice #invoicePrintArea .invoice-document-label { background: #fff !important; }
        body.printing-invoice #invoicePrintArea .invoice-grand-total strong:last-child { color: #000; }
        body.printing-invoice #invoicePrintArea .table-responsive { overflow: visible !important; }
        body.printing-invoice #invoicePrintArea .table { font-size: 8pt; }
    }
</style>
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <div>
        <h1 class="h3 mb-1">Kasir KomikStore</h1>
        <div class="text-muted">{{ now()->translatedFormat('l, d F Y H:i') }} · {{ auth()->user()->name }}</div>
    </div>
    <a class="btn btn-outline-secondary" href="{{ route('kasir.held') }}">Transaksi ditahan</a>
</div>

@if($held)
    <div class="alert alert-warning">
        Melanjutkan transaksi <strong>{{ $held->reference }}</strong>.
        Stok dan harga akan diperiksa kembali saat transaksi dibayar.
    </div>
@endif
@if($unavailableItems > 0)
    <div class="alert alert-danger">
        {{ $unavailableItems }} komik dari transaksi sebelumnya sudah tidak tersedia. Periksa keranjang sebelum melanjutkan.
    </div>
@endif

<div class="row g-3">
    <section class="col-lg-7" aria-label="Daftar komik">
        <div class="card mb-3">
            <div class="card-header fw-semibold">Tambah Komik · cari judul atau scan barcode</div>
            <div class="card-body">
                <label for="productSearch" class="form-label">Judul / barcode</label>
                <input id="productSearch" class="form-control" type="search" autocomplete="off"
                       placeholder="Ketik judul atau scan barcode lalu tekan Enter">
                <div id="searchMessage" class="form-text" aria-live="polite">
                    Pemindai barcode yang bertindak seperti keyboard bisa langsung digunakan.
                </div>
                <div id="productResults" class="list-group mt-2" role="listbox"></div>
            </div>
        </div>

        <div class="d-flex justify-content-between align-items-center mb-2">
            <h2 class="h5 mb-0">Komik tersedia</h2>
            <span class="text-muted small">{{ $comics->total() }} judul</span>
        </div>
        <div class="row g-2">
            @forelse($comics as $comic)
                <div class="col-md-6">
                    <div class="card h-100">
                        <div class="card-body d-flex flex-column">
                            <h3 class="h6">{{ $comic->title }}</h3>
                            <div class="small text-muted mb-2">
                                {{ $comic->category->name }} · Stok {{ $comic->stock }}
                                @if($comic->barcode) · Barcode {{ $comic->barcode }} @endif
                            </div>
                            <div class="fw-bold text-primary mb-2">Rp {{ number_format($comic->price, 0, ',', '.') }}</div>
                            <button type="button" class="btn btn-sm btn-outline-primary mt-auto js-add-comic"
                                    data-id="{{ $comic->id }}" data-price="{{ $comic->price }}"
                                    data-title="{{ $comic->title }}" data-stock="{{ $comic->stock }}">
                                Tambahkan
                            </button>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12"><div class="alert alert-info">Belum ada komik dengan stok tersedia.</div></div>
            @endforelse
        </div>
        <div class="mt-3">{{ $comics->links() }}</div>
    </section>

    <section class="col-lg-5" aria-label="Keranjang dan pembayaran">
        <form id="checkoutForm" method="POST" action="{{ route('kasir.checkout') }}" class="card">
            @csrf
            <input id="heldTransactionId" type="hidden" name="held_transaction_id" value="{{ $held?->id }}">
            <div class="card-header d-flex justify-content-between align-items-center">
                <strong>Keranjang belanja</strong>
                <span id="itemCount" class="badge text-bg-secondary">0 item</span>
            </div>
            <div id="cart" class="card-body">
                <p class="text-muted text-center my-3">Keranjang masih kosong.</p>
            </div>
            <div class="card-body border-top">
                <div class="row g-2 mb-3">
                    <div class="col-12">
                        <label class="form-label" for="customerName">Pelanggan (opsional)</label>
                        <input id="customerName" class="form-control" name="customer_name" maxlength="255"
                               value="{{ old('customer_name', $draft['customer_name'] ?? '') }}" placeholder="Umum">
                    </div>
                    <div class="col-12">
                        <label class="form-label" for="customerContact">No. telepon / member (opsional)</label>
                        <input id="customerContact" class="form-control" name="customer_contact" maxlength="50"
                               value="{{ old('customer_contact', $draft['customer_contact'] ?? '') }}">
                    </div>
                </div>

                <div class="d-flex justify-content-between py-1"><span>Total item</span><strong id="totalQuantity">0</strong></div>
                <div class="d-flex justify-content-between py-1"><span>Subtotal</span><strong id="subtotalDisplay">Rp 0</strong></div>
                <div class="row g-2 align-items-center py-1">
                    <label class="col-7 col-form-label" for="discountPercent">Diskon (%)</label>
                    <div class="col-5"><input id="discountPercent" class="form-control form-control-sm text-end"
                        type="number" name="discount_percent" min="0" max="100" step="0.01"
                        value="{{ old('discount_percent', $draft['discount_percent'] ?? 0) }}"></div>
                </div>
                <div class="row g-2 align-items-center py-1">
                    <label class="col-7 col-form-label" for="discountAmount">Diskon nominal (Rp)</label>
                    <div class="col-5"><input id="discountAmount" class="form-control form-control-sm text-end"
                        type="number" name="discount_amount" min="0" step="0.01"
                        value="{{ old('discount_amount', $draft['discount_amount'] ?? 0) }}"></div>
                </div>
                <div class="row g-2 align-items-center py-1">
                    <label class="col-7 col-form-label" for="tax">Pajak / PPN (Rp)</label>
                    <div class="col-5"><input id="tax" class="form-control form-control-sm text-end"
                        type="number" name="tax" min="0" step="0.01"
                        value="{{ old('tax', $draft['tax'] ?? 0) }}"></div>
                </div>
                <div class="row g-2 align-items-center py-1">
                    <label class="col-7 col-form-label" for="otherFee">Biaya lain (Rp)</label>
                    <div class="col-5"><input id="otherFee" class="form-control form-control-sm text-end"
                        type="number" name="other_fee" min="0" step="0.01"
                        value="{{ old('other_fee', $draft['other_fee'] ?? 0) }}"></div>
                </div>
                <div class="border-top mt-2 pt-3 d-flex justify-content-between fs-5">
                    <strong>Total akhir</strong><strong id="grandTotal">Rp 0</strong>
                </div>

                <label class="form-label mt-3" for="paymentMethod">Metode pembayaran</label>
                <select id="paymentMethod" class="form-select" name="payment_method" required>
                    @foreach(['cash' => 'Tunai', 'qris' => 'QRIS', 'debit' => 'Debit', 'credit' => 'Kredit', 'e_wallet' => 'E-Wallet', 'transfer' => 'Transfer'] as $value => $label)
                        <option value="{{ $value }}" @selected(old('payment_method', $draft['payment_method'] ?? 'cash') === $value)>{{ $label }}</option>
                    @endforeach
                </select>

                <label class="form-label mt-3" for="paid">Uang dibayar (Rp)</label>
                <input id="paid" class="form-control form-control-lg text-end" type="number" name="paid"
                       min="0" step="0.01" value="{{ old('paid', $draft['paid'] ?? '') }}" required>
                <div id="quickPayments" class="d-flex flex-wrap gap-2 mt-2"></div>
                <div class="d-flex justify-content-between mt-2">
                    <span>Kembalian</span><strong id="changeDisplay">Rp 0</strong>
                </div>
                <div id="paymentMessage" class="small mt-1" aria-live="polite"></div>

                <div class="d-grid gap-2 mt-3">
                    <button class="btn btn-warning" type="submit" formaction="{{ route('kasir.hold') }}"
                            formnovalidate>Tahan transaksi</button>
                    <div class="d-flex gap-2">
                        <button id="cancelTransaction" class="btn btn-outline-danger flex-fill" type="button">Batal</button>
                        <button id="payButton" class="btn btn-success flex-fill" type="submit">Bayar &amp; tampilkan invoice</button>
                    </div>
                </div>
            </div>
        </form>
    </section>
</div>

<div id="checkoutError" class="alert alert-danger mt-3 d-none" role="alert" aria-live="assertive"></div>

<div class="modal fade" id="invoiceModal" tabindex="-1" aria-labelledby="invoiceModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <div class="d-flex align-items-center gap-3">
                    <span class="invoice-success-icon" aria-hidden="true">✓</span>
                    <div>
                        <h2 class="modal-title h5 mb-0" id="invoiceModalTitle">Transaksi berhasil</h2>
                        <div class="invoice-number" id="invoiceNumber"></div>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body" id="invoicePrintArea">
                <div class="text-center mb-3">
                    <div class="invoice-brand">{{ config('escpos.store_name', 'KomikStore') }}</div>
                    @if(config('escpos.store_address'))<div class="invoice-store-details">{{ config('escpos.store_address') }}</div>@endif
                    @if(config('escpos.store_phone'))<div class="invoice-store-details">{{ config('escpos.store_phone') }}</div>@endif
                    <div class="invoice-document-label">BUKTI PEMBAYARAN</div>
                </div>
                <div class="row g-3 invoice-meta">
                    <div class="col-6 invoice-meta-item">Tanggal &amp; waktu<strong id="invoiceDate"></strong></div>
                    <div class="col-6 invoice-meta-item">Kasir<strong id="invoiceCashier"></strong></div>
                    <div class="col-6 invoice-meta-item" id="invoiceCustomerRow">Pelanggan<strong id="invoiceCustomer"></strong></div>
                    <div class="col-6 invoice-meta-item" id="invoiceContactRow">Kontak<strong id="invoiceContact"></strong></div>
                </div>
                <div class="table-responsive">
                    <table class="table table-sm invoice-table">
                        <thead><tr><th>Produk</th><th class="text-end">Qty</th><th class="text-end">Harga</th><th class="text-end">Jumlah</th></tr></thead>
                        <tbody id="invoiceItems"></tbody>
                    </table>
                </div>
                <div class="invoice-totals">
                    <div class="d-flex justify-content-between invoice-total-row"><span>Subtotal</span><strong id="invoiceSubtotal"></strong></div>
                    <div class="d-flex justify-content-between invoice-total-row" id="invoiceDiscountRow"><span>Diskon</span><span id="invoiceDiscount"></span></div>
                    <div class="d-flex justify-content-between invoice-total-row" id="invoiceTaxRow"><span>Pajak</span><span id="invoiceTax"></span></div>
                    <div class="d-flex justify-content-between invoice-total-row" id="invoiceFeeRow"><span>Biaya lain</span><span id="invoiceFee"></span></div>
                    <div class="d-flex justify-content-between align-items-center invoice-grand-total"><strong>Total pembayaran</strong><strong id="invoiceTotal"></strong></div>
                    <div class="d-flex justify-content-between invoice-total-row"><span>Jumlah dibayar</span><span id="invoicePaid"></span></div>
                    <div class="d-flex justify-content-between invoice-total-row"><span>Kembalian</span><span id="invoiceChange"></span></div>
                    <div class="d-flex justify-content-between invoice-total-row"><span>Metode pembayaran</span><span id="invoicePaymentMethod"></span></div>
                </div>
                <div class="text-center invoice-thank-you">Terima kasih telah berbelanja di KomikStore.</div>
            </div>
            <div class="modal-footer">
                <div class="invoice-action-row invoice-actions">
                    <a id="downloadInvoice" class="btn btn-outline-primary" href="#">Unduh invoice PDF</a>
                    <button id="printBrowserInvoice" type="button" class="btn btn-outline-secondary">Cetak melalui browser</button>
                </div>
                <div class="invoice-printer-panel invoice-actions">
                    <div>
                        <label for="thermalPrinter">Printer thermal</label>
                        <select id="thermalPrinter" class="form-select" aria-label="Pilih printer thermal">
                        <option value="">Memuat printer aktif...</option>
                        </select>
                    </div>
                    <button id="printThermalInvoice" type="button" class="btn btn-primary" disabled>Cetak struk</button>
                </div>
                <div class="d-flex justify-content-between align-items-start gap-2 invoice-help invoice-actions">
                    <span id="printerMessage" aria-live="polite">Pilihan printer untuk cetak browser akan tersedia di dialog cetak.</span>
                    <button id="retryPrinterLoad" type="button" class="btn btn-sm btn-outline-secondary flex-shrink-0">
                        Perbarui daftar
                    </button>
                </div>
                <div class="invoice-help" id="thermalPrintMessage" aria-live="polite"></div>
                <button type="button" class="btn btn-success invoice-done-button invoice-actions" data-bs-dismiss="modal">Selesai · transaksi baru</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
(() => {
    const cart = @js($initialCart);
    const cartElement = document.getElementById('cart');
    const form = document.getElementById('checkoutForm');
    const searchInput = document.getElementById('productSearch');
    const searchResults = document.getElementById('productResults');
    const searchMessage = document.getElementById('searchMessage');
    const paidInput = document.getElementById('paid');
    const paymentMethod = document.getElementById('paymentMethod');
    const payButton = document.getElementById('payButton');
    const heldInput = document.getElementById('heldTransactionId');
    const checkoutError = document.getElementById('checkoutError');
    const invoiceModalElement = document.getElementById('invoiceModal');
    const invoiceModal = bootstrap.Modal.getOrCreateInstance(invoiceModalElement);
    const thermalPrinter = document.getElementById('thermalPrinter');
    const thermalPrintButton = document.getElementById('printThermalInvoice');
    const thermalPrintMessage = document.getElementById('thermalPrintMessage');
    const printerMessage = document.getElementById('printerMessage');
    const retryPrinterLoad = document.getElementById('retryPrinterLoad');
    const printerEndpoint = new URL('{{ route('kasir.printers') }}');
    const sameOriginPrinterEndpoint = new URL(
        `${printerEndpoint.pathname}${printerEndpoint.search}`,
        window.location.origin
    );
    const cashMode = () => paymentMethod.value === 'cash';
    const rupiah = value => `Rp ${Math.round(value).toLocaleString('id-ID')}`;

    function setInvoiceValue(id, value) {
        document.getElementById(id).textContent = value ?? '';
    }

    function showInvoice(invoice, downloadUrl, printUrl) {
        const discount = (Number(invoice.subtotal) * Number(invoice.discount_percent) / 100) + Number(invoice.discount_amount);
        setInvoiceValue('invoiceNumber', `No. ${invoice.invoice}`);
        setInvoiceValue('invoiceDate', invoice.date);
        setInvoiceValue('invoiceCashier', invoice.cashier);
        setInvoiceValue('invoiceCustomer', invoice.customer_name);
        setInvoiceValue('invoiceContact', invoice.customer_contact);
        document.getElementById('invoiceCustomerRow').hidden = !invoice.customer_name;
        document.getElementById('invoiceContactRow').hidden = !invoice.customer_contact;
        setInvoiceValue('invoiceSubtotal', rupiah(invoice.subtotal));
        setInvoiceValue('invoiceDiscount', `-${rupiah(discount)}`);
        setInvoiceValue('invoiceTax', rupiah(invoice.tax));
        setInvoiceValue('invoiceFee', rupiah(invoice.other_fee));
        setInvoiceValue('invoiceTotal', rupiah(invoice.total));
        setInvoiceValue('invoicePaid', rupiah(invoice.paid));
        setInvoiceValue('invoiceChange', rupiah(invoice.change));
        setInvoiceValue('invoicePaymentMethod', String(invoice.payment_method).replaceAll('_', ' ').toUpperCase());
        document.getElementById('invoiceDiscountRow').hidden = discount <= 0;
        document.getElementById('invoiceTaxRow').hidden = Number(invoice.tax) <= 0;
        document.getElementById('invoiceFeeRow').hidden = Number(invoice.other_fee) <= 0;

        const rows = document.getElementById('invoiceItems');
        rows.replaceChildren();
        invoice.items.forEach(item => {
            const row = document.createElement('tr');
            [item.title, item.quantity, rupiah(item.price), rupiah(item.subtotal)].forEach((value, index) => {
                const cell = document.createElement('td');
                cell.textContent = value;
                if (index > 0) cell.className = 'text-end text-nowrap';
                row.append(cell);
            });
            rows.append(row);
        });

        document.getElementById('downloadInvoice').href = downloadUrl;
        document.getElementById('printThermalInvoice').dataset.printUrl = printUrl;
        thermalPrintMessage.textContent = '';
        thermalPrintMessage.className = 'w-100 small';
        invoiceModal.show();
        loadThermalPrinters();
    }

    async function loadThermalPrinters() {
        thermalPrinter.replaceChildren();
        retryPrinterLoad.disabled = true;
        retryPrinterLoad.textContent = 'Memuat...';
        const loading = document.createElement('option');
        loading.value = '';
        loading.textContent = 'Memuat printer aktif...';
        thermalPrinter.append(loading);
        thermalPrinter.disabled = true;
        thermalPrintButton.disabled = true;

        try {
            const response = await fetch(sameOriginPrinterEndpoint, {
                headers: {'Accept': 'application/json'},
                cache: 'no-store',
                credentials: 'same-origin'
            });
            if (!response.headers.get('content-type')?.includes('application/json')) {
                throw new Error('Respons daftar printer tidak valid.');
            }
            const data = await response.json();
            if (!response.ok) throw new Error(data.message || 'Daftar printer gagal dimuat.');

            thermalPrinter.replaceChildren();
            if (!data.printers.length) {
                const option = document.createElement('option');
                option.value = '';
                option.textContent = 'Tidak ada printer Windows aktif';
                thermalPrinter.append(option);
                printerMessage.textContent = data.message || 'Printer thermal tidak tersedia. Cetak browser tetap dapat digunakan.';
                retryPrinterLoad.textContent = 'Coba lagi';
                retryPrinterLoad.disabled = false;
                return;
            }

            data.printers.forEach(printer => {
                const option = document.createElement('option');
                option.value = printer.name;
                option.textContent = `${printer.name} — ${printer.status}${printer.thermal ? ' (thermal)' : printer.default ? ' (default)' : ''}`;
                thermalPrinter.append(option);
            });
            const preferredPrinter = data.printers.find(printer => printer.thermal)
                || data.printers.find(printer => printer.default)
                || data.printers[0];
            thermalPrinter.value = preferredPrinter.name;
            thermalPrinter.disabled = false;
            thermalPrintButton.disabled = !thermalPrinter.value;
            printerMessage.textContent = `${data.printers.length} printer aktif ditemukan. ${preferredPrinter.name} dipilih untuk cetak thermal. Cetak browser menggunakan dialog printer komputer.`;
            retryPrinterLoad.textContent = 'Perbarui daftar';
            retryPrinterLoad.disabled = false;
        } catch (error) {
            thermalPrinter.replaceChildren();
            const option = document.createElement('option');
            option.value = '';
            option.textContent = 'Printer Windows tidak dapat dimuat';
            thermalPrinter.append(option);
            printerMessage.textContent = error.message || 'Daftar printer gagal dimuat.';
            retryPrinterLoad.textContent = 'Coba lagi';
            retryPrinterLoad.disabled = false;
        }
    }

    retryPrinterLoad.addEventListener('click', loadThermalPrinters);

    thermalPrinter.addEventListener('change', () => {
        thermalPrintButton.disabled = !thermalPrinter.value;
    });

    document.getElementById('printBrowserInvoice').addEventListener('click', () => {
        document.body.classList.add('printing-invoice');
        window.print();
        window.setTimeout(() => document.body.classList.remove('printing-invoice'), 500);
    });

    thermalPrintButton.addEventListener('click', async () => {
        if (!thermalPrinter.value) return;
        thermalPrintButton.disabled = true;
        thermalPrintMessage.textContent = 'Mengirim invoice ke printer...';
        try {
            const response = await fetch(thermalPrintButton.dataset.printUrl, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': form.querySelector('input[name="_token"]').value
                },
                credentials: 'same-origin',
                body: JSON.stringify({printer_name: thermalPrinter.value})
            });
            if (!response.headers.get('content-type')?.includes('application/json')) {
                throw new Error('Respons printer tidak valid. Muat ulang halaman dan coba kembali.');
            }
            const data = await response.json();
            if (!response.ok) throw new Error(data.message || 'Gagal mencetak invoice.');
            thermalPrintMessage.textContent = data.message;
            thermalPrintMessage.className = 'w-100 small text-success';
        } catch (error) {
            thermalPrintMessage.textContent = error.message || 'Gagal mencetak invoice.';
            thermalPrintMessage.className = 'w-100 small text-danger';
        } finally {
            thermalPrintButton.disabled = !thermalPrinter.value;
        }
    });

    function updateTotals() {
        const lines = Object.values(cart);
        const quantity = lines.reduce((sum, item) => sum + item.qty, 0);
        const subtotal = lines.reduce((sum, item) => sum + item.price * item.qty, 0);
        const percentInput = document.getElementById('discountPercent');
        const amountInput = document.getElementById('discountAmount');
        const percent = Number(percentInput.value || 0);
        const amount = Number(amountInput.value || 0);
        const percentageDiscount = subtotal * percent / 100;
        const discount = percentageDiscount + amount;
        const invalidDiscount = discount > subtotal;
        const tax = Number(document.getElementById('tax').value || 0);
        const otherFee = Number(document.getElementById('otherFee').value || 0);
        const total = Math.max(0, subtotal - discount + tax + otherFee);

        document.getElementById('totalQuantity').textContent = quantity;
        document.getElementById('itemCount').textContent = `${quantity} item`;
        document.getElementById('subtotalDisplay').textContent = rupiah(subtotal);
        document.getElementById('grandTotal').textContent = rupiah(total);
        if (!cashMode()) {
            paidInput.value = total.toFixed(2);
        }
        paidInput.readOnly = !cashMode();
        const paid = Number(paidInput.value || 0);
        document.getElementById('changeDisplay').textContent = rupiah(Math.max(0, paid - total));
        document.getElementById('paymentMessage').textContent = invalidDiscount
            ? 'Total diskon tidak boleh melebihi subtotal.'
            : (!cashMode() || paid >= total ? '' : 'Uang pembayaran masih kurang.');
        document.getElementById('paymentMessage').className = `small mt-1 ${invalidDiscount || (cashMode() && paid < total) ? 'text-danger' : 'text-success'}`;
        renderQuickPayments(total);
        payButton.disabled = quantity === 0 || invalidDiscount || (cashMode() && paid < total);
        return {subtotal, total};
    }

    function addComic(comic) {
        const id = String(comic.id);
        const stock = Number(comic.stock);
        if (stock < 1) {
            searchMessage.textContent = 'Stok komik ini sudah habis.';
            return;
        }
        if (cart[id]) {
            if (cart[id].qty >= stock) {
                searchMessage.textContent = `Stok ${comic.title} hanya ${stock}.`;
                return;
            }
            cart[id].qty += 1;
        } else {
            cart[id] = {id: Number(comic.id), title: comic.title, price: Number(comic.price), qty: 1, stock};
        }

        searchMessage.textContent = `${comic.title} ditambahkan ke keranjang.`;
        renderCart();
    }

    function renderQuickPayments(total) {
        const container = document.getElementById('quickPayments');
        container.replaceChildren();
        if (!cashMode() || total <= 0) return;

        const amounts = [...new Set([
            total,
            Math.ceil(total / 10000) * 10000,
            Math.ceil(total / 50000) * 50000,
            Math.ceil(total / 100000) * 100000
        ])];

        amounts.forEach(amount => {
            const button = document.createElement('button');
            button.type = 'button';
            button.className = 'btn btn-sm btn-outline-secondary';
            button.textContent = amount === total ? 'Uang pas' : rupiah(amount);
            button.addEventListener('click', () => {
                paidInput.value = amount;
                updateTotals();
            });
            container.append(button);
        });
    }

    function renderCart() {
        cartElement.replaceChildren();
        const items = Object.values(cart);
        if (items.length === 0) {
            const empty = document.createElement('p');
            empty.className = 'text-muted text-center my-3';
            empty.textContent = 'Keranjang masih kosong.';
            cartElement.append(empty);
        }

        items.forEach((item, index) => {
            const row = document.createElement('div');
            row.className = 'border-bottom py-2';

            const heading = document.createElement('div');
            heading.className = 'd-flex justify-content-between gap-2';
            const title = document.createElement('strong');
            title.textContent = `${index + 1}. ${item.title}`;
            const subtotal = document.createElement('span');
            subtotal.className = 'text-nowrap';
            subtotal.textContent = rupiah(item.price * item.qty);
            heading.append(title, subtotal);

            const controls = document.createElement('div');
            controls.className = 'd-flex align-items-center gap-2 mt-2';
            const decrease = document.createElement('button');
            decrease.type = 'button';
            decrease.className = 'btn btn-sm btn-outline-secondary';
            decrease.textContent = '−';
            decrease.setAttribute('aria-label', `Kurangi ${item.title}`);
            decrease.addEventListener('click', () => changeQuantity(item.id, -1));
            const quantity = document.createElement('span');
            quantity.className = 'px-2';
            quantity.textContent = item.qty;
            const increase = document.createElement('button');
            increase.type = 'button';
            increase.className = 'btn btn-sm btn-outline-secondary';
            increase.textContent = '+';
            increase.setAttribute('aria-label', `Tambah ${item.title}`);
            increase.addEventListener('click', () => changeQuantity(item.id, 1));
            const remove = document.createElement('button');
            remove.type = 'button';
            remove.className = 'btn btn-sm btn-outline-danger ms-auto';
            remove.textContent = 'Hapus';
            remove.addEventListener('click', () => {
                delete cart[item.id];
                renderCart();
            });
            controls.append(decrease, quantity, increase, remove);

            const comicId = document.createElement('input');
            comicId.type = 'hidden';
            comicId.name = `items[${index}][comic_id]`;
            comicId.value = item.id;
            const comicQuantity = document.createElement('input');
            comicQuantity.type = 'hidden';
            comicQuantity.name = `items[${index}][quantity]`;
            comicQuantity.value = item.qty;
            row.append(heading, controls, comicId, comicQuantity);
            cartElement.append(row);
        });

        updateTotals();
    }

    function changeQuantity(id, amount) {
        const item = cart[id];
        if (!item) return;
        item.qty += amount;
        if (item.qty < 1) delete cart[id];
        if (cart[id] && cart[id].qty > cart[id].stock) cart[id].qty = cart[id].stock;
        renderCart();
    }

    function addResult(comic) {
        const button = document.createElement('button');
        button.type = 'button';
        button.className = 'list-group-item list-group-item-action d-flex justify-content-between gap-3';
        const details = document.createElement('span');
        details.textContent = `${comic.title}${comic.barcode ? ` · ${comic.barcode}` : ''} · Stok ${comic.stock}`;
        const price = document.createElement('strong');
        price.className = 'text-nowrap';
        price.textContent = rupiah(Number(comic.price));
        button.append(details, price);
        button.addEventListener('click', () => {
            addComic(comic);
            searchInput.value = '';
            searchResults.replaceChildren();
            searchInput.focus();
        });
        searchResults.append(button);
    }

    async function lookupProducts(keyword) {
        const response = await fetch(`{{ route('kasir.products.search') }}?q=${encodeURIComponent(keyword)}`, {
            headers: {'Accept': 'application/json'},
            credentials: 'same-origin'
        });
        if (!response.ok) throw new Error('Pencarian komik gagal. Coba kembali.');
        return response.json();
    }

    let searchTimer;
    searchInput.addEventListener('input', () => {
        clearTimeout(searchTimer);
        const keyword = searchInput.value.trim();
        if (!keyword) {
            searchResults.replaceChildren();
            return;
        }
        searchTimer = setTimeout(async () => {
            try {
                const products = await lookupProducts(keyword);
                searchResults.replaceChildren();
                products.forEach(addResult);
                if (products.length === 0) searchMessage.textContent = 'Komik tidak ditemukan atau stok habis.';
            } catch (error) {
                searchMessage.textContent = error.message;
            }
        }, 200);
    });

    searchInput.addEventListener('keydown', async event => {
        if (event.key !== 'Enter') return;
        event.preventDefault();
        const barcode = searchInput.value.trim();
        if (!barcode) return;

        try {
            const products = await lookupProducts(barcode);
            const exact = products.find(comic => comic.barcode === barcode);
            if (exact) {
                addComic(exact);
                searchInput.value = '';
                searchResults.replaceChildren();
            } else if (products.length === 1) {
                addComic(products[0]);
                searchInput.value = '';
                searchResults.replaceChildren();
            } else if (products.length === 0) {
                searchMessage.textContent = 'Barcode tidak ditemukan atau stok habis.';
            }
        } catch (error) {
            searchMessage.textContent = error.message;
        }
    });

    document.querySelectorAll('.js-add-comic').forEach(button => {
        button.addEventListener('click', () => addComic({
            id: button.dataset.id,
            price: button.dataset.price,
            title: button.dataset.title,
            stock: button.dataset.stock
        }));
    });

    ['discountPercent', 'discountAmount', 'tax', 'otherFee'].forEach(id => {
        document.getElementById(id).addEventListener('input', updateTotals);
    });
    paidInput.addEventListener('input', updateTotals);
    paymentMethod.addEventListener('change', updateTotals);

    document.getElementById('cancelTransaction').addEventListener('click', () => {
        Object.keys(cart).forEach(id => delete cart[id]);
        form.reset();
        heldInput.value = '';
        document.getElementById('customerName').value = '';
        document.getElementById('customerContact').value = '';
        document.getElementById('discountPercent').value = 0;
        document.getElementById('discountAmount').value = 0;
        document.getElementById('tax').value = 0;
        document.getElementById('otherFee').value = 0;
        paymentMethod.value = 'cash';
        searchMessage.textContent = 'Transaksi dibatalkan.';
        renderCart();
    });

    document.addEventListener('keydown', event => {
        if (event.key === 'F2') {
            event.preventDefault();
            searchInput.focus();
        } else if (event.key === 'F4') {
            event.preventDefault();
            paidInput.focus();
        } else if (event.key === 'Escape') {
            document.getElementById('cancelTransaction').click();
        }
    });

    form.addEventListener('submit', async event => {
        if (event.submitter && event.submitter.formAction.endsWith('/hold')) return;
        event.preventDefault();
        if (Object.keys(cart).length === 0) {
            searchMessage.textContent = 'Tambahkan komik sebelum memproses pembayaran.';
            return;
        }

        checkoutError.classList.add('d-none');
        checkoutError.textContent = '';
        const submitButtons = [...form.querySelectorAll('button[type="submit"]')];
        submitButtons.forEach(button => button.disabled = true);
        try {
            const response = await fetch(form.action, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                credentials: 'same-origin',
                body: new FormData(form)
            });
            if (!response.headers.get('content-type')?.includes('application/json')) {
                throw new Error('Respons checkout tidak valid. Sesi Anda mungkin telah berakhir; muat ulang halaman dan coba kembali.');
            }
            const data = await response.json();
            if (!response.ok) {
                const messages = data.errors ? Object.values(data.errors).flat() : [data.message || 'Transaksi gagal diproses.'];
                throw new Error(messages.join(' '));
            }

            Object.keys(cart).forEach(id => delete cart[id]);
            form.reset();
            heldInput.value = '';
            document.getElementById('discountPercent').value = 0;
            document.getElementById('discountAmount').value = 0;
            document.getElementById('tax').value = 0;
            document.getElementById('otherFee').value = 0;
            paymentMethod.value = 'cash';
            renderCart();
            showInvoice(data.invoice, data.download_url, data.print_url);
        } catch (error) {
            checkoutError.textContent = error.message || 'Transaksi gagal diproses. Periksa koneksi lalu coba kembali.';
            checkoutError.classList.remove('d-none');
            checkoutError.scrollIntoView({behavior: 'smooth', block: 'center'});
        } finally {
            submitButtons.forEach(button => button.disabled = false);
            updateTotals();
        }
    });

    renderCart();
    searchInput.focus();
})();
</script>
@endsection
