<div style="font-size: 0.92em; line-height: 1.55; color: #444; max-height: 90px; overflow: hidden;">
    {!! nl2br(e(\Illuminate\Support\Str::limit(strip_tags($template->prompt), 240))) !!}
</div>
<div class="mt-2 text-muted small float-end">
    <i class="bi bi-chat-square-text"></i> 
    {{ \Illuminate\Support\Str::wordCount(strip_tags($template->prompt)) }} palavras • 
    {{ \Illuminate\Support\Str::length(strip_tags($template->prompt)) }} caracteres
</div>