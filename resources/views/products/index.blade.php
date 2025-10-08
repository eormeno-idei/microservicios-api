{{-- filepath: /workspaces/microservicios-api/resources/views/products/index.blade.php --}}
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Productos - Microservicios API</title>
    <link rel="stylesheet" href="{{ asset('css/products.css') }}">
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🛍️ Catálogo de Productos</h1>
            <p>Descubre nuestra amplia selección de productos</p>
        </div>

        <div class="controls">
            <div class="search-box">
                <input type="text" id="searchInput" placeholder="🔍 Buscar productos...">
            </div>
            <select id="categoryFilter" class="filter-select">
                <option value="">Todas las categorías</option>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                @endforeach
            </select>
            <select id="stockFilter" class="filter-select">
                <option value="">Todo el stock</option>
                <option value="available">Disponible</option>
                <option value="low">Stock bajo</option>
                <option value="out">Sin stock</option>
            </select>
        </div>

        <div class="products-grid" id="productsGrid">
            @php
                // Manejar tanto arrays como objetos paginados
                $productItems = is_object($products) && method_exists($products, 'items')
                    ? $products->items()
                    : (is_array($products) ? $products : collect($products));
            @endphp
            @forelse($productItems as $product)
                <div class="product-card"
                     data-name="{{ strtolower($product->name) }}"
                     data-category="{{ $product->category ? strtolower($product->category->name) : '' }}"
                     data-stock="{{ $product->stock }}">

                    <div class="stock-badge
                        @if($product->stock > 10) stock-available
                        @elseif($product->stock > 0) stock-low
                        @else stock-out
                        @endif">
                        @if($product->stock > 10) En Stock
                        @elseif($product->stock > 0) Stock Bajo
                        @else Sin Stock
                        @endif
                    </div>

                    <div class="product-image">
                        @if($product->image_url)
                            <img src="{{ $product->image_url }}" alt="{{ $product->name }}" style="width: 100%; height: 100%; object-fit: cover;">
                        @else
                            📦
                        @endif
                    </div>

                    <div class="product-content">
                        @if($product->category)
                            <span class="category-tag">{{ $product->category->name }}</span>
                        @endif

                        <h3 class="product-title">{{ $product->name }}</h3>

                        @if($product->description)
                            <p class="product-description">{{ $product->description }}</p>
                        @endif

                        <div class="product-price">${{ number_format($product->price, 2) }}</div>

                        <div class="product-details">
                            <div class="detail-item">
                                <span class="detail-label">Stock</span>
                                <span class="detail-value">{{ $product->stock }}</span>
                            </div>
                            @if($product->weight)
                                <div class="detail-item">
                                    <span class="detail-label">Peso</span>
                                    <span class="detail-value">{{ $product->weight }}kg</span>
                                </div>
                            @endif
                            <div class="detail-item">
                                <span class="detail-label">Rating</span>
                                <span class="detail-value">
                                    @if($product->averageRating())
                                        ⭐ {{ number_format($product->averageRating(), 1) }}
                                    @else
                                        Sin rating
                                    @endif
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="no-products">
                    <div class="no-products-icon">📦</div>
                    <h3>No hay productos disponibles</h3>
                    <p>Vuelve más tarde para ver nuestros productos</p>
                </div>
            @endforelse
        </div>

        @if(is_object($products) && method_exists($products, 'hasPages') && $products->hasPages())
            <div class="pagination-wrapper">
                <div class="pagination-info">
                    <p>Mostrando {{ $products->firstItem() }} - {{ $products->lastItem() }} de {{ $products->total() }} productos</p>
                </div>
                <div class="pagination">
                    {{-- Previous Page Link --}}
                    @if ($products->onFirstPage())
                        <span class="pagination-link disabled">« Anterior</span>
                    @else
                        <a href="{{ $products->previousPageUrl() }}" class="pagination-link">« Anterior</a>
                    @endif

                    {{-- Pagination Elements --}}
                    @foreach ($products->getUrlRange(1, $products->lastPage()) as $page => $url)
                        @if ($page == $products->currentPage())
                            <span class="pagination-link active">{{ $page }}</span>
                        @else
                            <a href="{{ $url }}" class="pagination-link">{{ $page }}</a>
                        @endif
                    @endforeach

                    {{-- Next Page Link --}}
                    @if ($products->hasMorePages())
                        <a href="{{ $products->nextPageUrl() }}" class="pagination-link">Siguiente »</a>
                    @else
                        <span class="pagination-link disabled">Siguiente »</span>
                    @endif
                </div>
            </div>
        @elseif(is_array($products) && count($products) > 0)
            <div class="pagination-wrapper">
                <div class="pagination-info">
                    <p>Mostrando {{ count($products) }} productos</p>
                </div>
            </div>
        @endif
    </div>

    <script src="{{ asset('js/products.js') }}"></script>
</body>
</html>
