@php
    use App\Models\Lead;
    $statuses = Lead::STATUS;
@endphp

<div class="grid grid-cols-1 md:grid-cols-4 gap-4">
    @foreach($statuses as $status)
        @php
            $cards = $leads[$status] ?? collect();
            $mostrarMais = $cards->count() > 5;
        @endphp

        <div class="bg-white rounded-xl shadow p-3">
            <h5 class="font-bold text-lg mb-3 capitalize">{{ str_replace('_', ' ', $status) }}</h5>

            @foreach($cards->take(5) as $lead)
                <div class="border rounded-lg p-2 mb-2">
                    <div class="font-semibold">{{ $lead->nome }}</div>
                    <div class="text-sm text-gray-500">{{ $lead->origem }}</div>
                </div>
            @endforeach

            @if($mostrarMais)
                <button class="text-sm text-blue-500 hover:underline" onclick="alert('Implementar ver mais')">
                    Ver mais ({{ $cards->count() - 5 }})
                </button>
            @endif
        </div>
    @endforeach
</div>
