<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Sample WP API project</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body>
        <h1>{{ $post->title }} </h1>
        <b>Author: {{ $post->author }}</b>
        <b>Category: {{ $post->category }}</b>
        <b>Created: {{ $post->created_at }}</b>
        <b>Updated: {{ $post->updated_at }}</b>

        <p>
            {!! $post->excerpt !!}
        </p>

        <p>
            {!! $post->content !!}
        </p>

        <!-- Listing all the equipments -->
        @foreach ($post->extraAttributes->equipment as $equipment)
            {{ Equipment::get($equipment) }}
        @endforeach
</body>
</html>