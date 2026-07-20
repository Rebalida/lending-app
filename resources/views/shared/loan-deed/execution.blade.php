{{-- Loan Deed — execution page (source PDF page 31).
     Renders captured signatures as <img> when present, blank lines otherwise. --}}
<div class="deed-page">
    <p class="deed-p"><strong>Executed as a deed:</strong></p>

    <p class="deed-p deed-dated">
        <strong>Dated:</strong>
        <span class="deed-dated-line">{{ !empty($d['signed_at']) ? \Illuminate\Support\Carbon::parse($d['signed_at'])->format('d F') : '' }}</span>
        {{ !empty($d['signed_at']) ? \Illuminate\Support\Carbon::parse($d['signed_at'])->format('Y') : date('Y') }}
    </p>

    {{-- Lender execution --}}
    <table class="deed-exec-table">
        <tr>
            <td class="deed-exec-left">
                <strong>EXECUTED</strong> by <strong>ZYA Capital Pty Ltd ACN 695 692 052</strong>
                in accordance with Section 127 of the <em>Corporations Act 2001</em> (Cth):
            </td>
            <td class="deed-exec-right">
                <div class="deed-sig-line"></div>
                Sole Director/Secretary
            </td>
        </tr>
    </table>

    {{-- Borrower company execution — one row per director --}}
    <table class="deed-exec-table">
        <tr>
            <td class="deed-exec-left">
                <strong>EXECUTED</strong> by <strong>{{ $d['borrower_name'] ?: '[insert]' }}
                {{ $d['borrower_acn'] ? 'ACN ' . $d['borrower_acn'] : ($d['borrower_abn'] ? 'ABN ' . $d['borrower_abn'] : '') }}</strong>
                in accordance with Section 127 of the <em>Corporations Act 2001</em> (Cth):
            </td>
            <td class="deed-exec-right">
                @forelse($d['directors'] as $director)
                    <div class="deed-sig-line"></div>
                    {{ $director['full_name'] }}<br>
                    <strong>Director</strong>
                    @if(!$loop->last)<br><br>@endif
                @empty
                    <div class="deed-sig-line"></div>
                    [insert]<br>
                    <strong>Director</strong>
                @endforelse
            </td>
        </tr>
    </table>

    {{-- Client (borrower/guarantor individual) execution --}}
    <table class="deed-exec-table">
        <tr>
            <td class="deed-exec-left">
                <strong>SIGNED, SEALED AND DELIVERED</strong> by
                {{ $d['guarantor_name'] ?: $d['borrower_name'] ?: '[Insert]' }} in the presence of:
                <br><br>
                @if(!empty($d['witness_signature']))
                    <img src="{{ $d['witness_signature'] }}" alt="Witness signature" class="deed-sig-img">
                @else
                    <div class="deed-sig-line"></div>
                @endif
                Witness signature
                <br><br>
                <div class="deed-sig-underline">{{ $d['witness_name'] ?: '' }}</div>
                Print name
            </td>
            <td class="deed-exec-right">
                @if(!empty($d['client_signature']))
                    <img src="{{ $d['client_signature'] }}" alt="Signature" class="deed-sig-img">
                @else
                    <div class="deed-sig-line"></div>
                @endif
                {{ $d['guarantor_name'] ?: $d['borrower_name'] ?: '[Insert]' }}<br>
                Signature
                @if(!empty($d['signed_at']))
                    <br><span class="deed-muted">Signed: {{ $d['signed_at'] }}</span>
                @endif
                @if(!empty($d['signed_ip']))
                    <br><span class="deed-muted">IP: {{ $d['signed_ip'] }}</span>
                @endif
            </td>
        </tr>
    </table>
</div>
