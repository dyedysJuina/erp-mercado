@props(['count' => 1, 'class' => 'h-4'])

@for ($i = 0; $i < $count; $i++)
    <div class="skeleton {{ $class }}" {{ $attributes }}>&nbsp;</div>
@endfor