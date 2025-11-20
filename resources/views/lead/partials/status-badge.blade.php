@if($lead->exists)
    <div class="mb-3">
        {!! $lead->status_badge !!}
    </div>
@endif
