<div class="btn-group" role="group">
    <a href="{{ $editRoute }}" class="btn btn-sm btn-primary" title="Editar">
        <i class="icon-pencil"></i>
    </a>

    @if(isset($viewRoute))
        <a href="{{ $viewRoute }}" class="btn btn-sm btn-info" title="Visualizar">
            <i class="icon-eye"></i>
        </a>
    @endif

    @if($delete ?? false)
        <button
            type="button"
            class="btn btn-sm btn-danger"
            onclick="confirmDelete('{{ $editRoute }}')"
            title="Excluir">
            <i class="icon-trash"></i>
        </button>
    @endif
</div>

<script>
function confirmDelete(route) {
    if (confirm('Tem certeza que deseja excluir esta proposta?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = route;

        const method = document.createElement('input');
        method.type = 'hidden';
        method.name = '_method';
        method.value = 'DELETE';
        form.appendChild(method);

        const token = document.createElement('input');
        token.type = 'hidden';
        token.name = '_token';
        token.value = '{{ csrf_token() }}';
        form.appendChild(token);

        document.body.appendChild(form);
        form.submit();
    }
}
</script>