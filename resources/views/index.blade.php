<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Sample WP API project</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body>
    @foreach($posts as $post)
        <h1> <a href="{{ url('/' . $post->slug) }}">{{ $post->title }}</a> </h1>
        <b>Author: {{ $post->author }}</b>
        <b>Category: {{ $post->category }}</b>
        <b>Created: {{ $post->created_at }}</b>
        <b>Updated: {{ $post->updated_at }}</b>

        <img src="{{ $post->featuredMedia->medium_large->source_url }}" alt="">

        <p>
            {!! $post->excerpt !!}
        </p>

        <p>
            {!! $post->content !!}
        </p>
    @endforeach
</body>
</html>