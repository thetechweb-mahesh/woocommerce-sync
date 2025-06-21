
    <h1>My Products</h1>
    <ul>
        @foreach ($products as $product)
            <li>{{ $product->name }} - ${{ $product->price }} - {{ $product->status }}</li>
        @endforeach
    </ul>
