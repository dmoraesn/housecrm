<div class="d-flex justify-content-center gap-2">

    {{-- Editar --}}
    <a href="{{ $editRoute }}" class="text-primary" title="Editar">
        <x-orchid-icon path="bs.pencil-square" class="icon-md" />
    </a>

    {{-- Visualizar --}}
    <a href="javascript:void(0)" class="text-secondary" title="Visualizar"
       onclick="window.platform.modal({ title: 'Visualizar Proposta', size: 'xl', body: `<iframe src='{{ $viewRoute }}' style='width:100%;height:80vh;border:none;'></iframe>` })">
        <x-orchid-icon path="bs.eye" class="icon-md" />
    </a>

    {{-- Arquivar --}}
    <form method="POST" action="{{ route('platform.propostas.archive') }}" class="d-inline">
        @csrf
        <input type="hidden" name="proposta" value="{{ $archiveId }}">
        <button type="submit" class="btn-icon text-danger" title="Arquivar">
            <x-orchid-icon path="bs.archive" class="icon-md" />
        </button>
    </form>

</div>
