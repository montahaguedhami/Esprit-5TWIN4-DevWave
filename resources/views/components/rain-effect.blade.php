@props([
    'count' => 40,
])

<div class="rain-container">
    @for ($i = 0; $i < $count; $i++)
        @php
            $left = rand(0, 100);
            $height = rand(20, 60);
            $duration = (rand(60, 140) / 100);
            $delay = (rand(0, 300) / 100);
            $opacity = (rand(30, 70) / 100);
        @endphp
        <div 
            class="raindrop" 
            style="left: {{ $left }}%; height: {{ $height }}px; animation-duration: {{ $duration }}s; animation-delay: {{ $delay }}s; opacity: {{ $opacity }};"
        ></div>
    @endfor
</div>
