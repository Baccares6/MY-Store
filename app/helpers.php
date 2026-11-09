<?php

if (!function_exists('getProductImageUrl')) {
    /**
     * Resolves product image to existing asset, valid URL, or aesthetic contextual fallback
     */
    function getProductImageUrl($imageName = null, $title = '', $catId = null)
    {
        if (empty($imageName)) {
            $imageName = '';
        }

        // Jika merupakan link URL penuh
        if (str_starts_with($imageName, 'http://') || str_starts_with($imageName, 'https://')) {
            if (str_contains($imageName, 'photo-1584992236310-6edddc08acff') || str_contains(strtolower($title), 'refrigerator') || str_contains(strtolower($title), 'kulkas')) {
                if (file_exists(public_path('product_images/lg_refrigerator.png'))) {
                    return asset('product_images/lg_refrigerator.png');
                }
            }
            return $imageName;
        }

        // Jika file gambar ada secara lokal di public/
        if (!empty($imageName)) {
            if (file_exists(public_path('product_images/' . $imageName))) {
                return asset('product_images/' . $imageName);
            }
            if (file_exists(public_path('images/' . $imageName))) {
                return asset('images/' . $imageName);
            }
            if (file_exists(public_path($imageName))) {
                return asset($imageName);
            }
        }

        // Resolusi gambar cerdas berbasis nama atau kata kunci produk
        $text = strtolower($title . ' ' . $imageName);

        if (str_contains($text, 'refrigerator') || str_contains($text, 'fridge') || str_contains($text, 'kulkas')) {
            if (file_exists(public_path('product_images/lg_refrigerator.png'))) {
                return asset('product_images/lg_refrigerator.png');
            }
            return 'https://images.unsplash.com/photo-1571175443880-49e1d25b2bc5?auto=format&fit=crop&w=500&q=80';
        }

        if (str_contains($text, 'samsung') || str_contains($text, 'galaxy')) {
            return 'https://images.unsplash.com/photo-1610945415295-d9bbf067e59c?auto=format&fit=crop&w=500&q=80';
        }
        if (str_contains($text, 'iphone') || str_contains($text, 'apple phone') || str_contains($text, '5s')) {
            return 'https://images.unsplash.com/photo-1592750475338-74b7b21085ab?auto=format&fit=crop&w=500&q=80';
        }
        if (str_contains($text, 'ipad') || str_contains($text, 'tablet')) {
            return 'https://images.unsplash.com/photo-1544244015-0df4b3ffc6b0?auto=format&fit=crop&w=500&q=80';
        }
        if (str_contains($text, 'laptop') || str_contains($text, 'macbook') || str_contains($text, 'hp') || str_contains($text, 'notebook')) {
            return 'https://images.unsplash.com/photo-1496181133206-80ce9b88a853?auto=format&fit=crop&w=500&q=80';
        }
        if (str_contains($text, 'watch') || str_contains($text, 'jam')) {
            return 'https://images.unsplash.com/photo-1523275335684-37898b6baf30?auto=format&fit=crop&w=500&q=80';
        }
        if (str_contains($text, 'headphone') || str_contains($text, 'headset') || str_contains($text, 'audio') || str_contains($text, 'earphone')) {
            return 'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?auto=format&fit=crop&w=500&q=80';
        }
        if (str_contains($text, 'camera') || str_contains($text, 'kamera') || str_contains($text, 'lens')) {
            return 'https://images.unsplash.com/photo-1526170375885-4d8ecf77b99f?auto=format&fit=crop&w=500&q=80';
        }
        if (str_contains($text, 'shoe') || str_contains($text, 'sepatu') || str_contains($text, 'sneaker') || str_contains($text, 'running')) {
            return 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?auto=format&fit=crop&w=500&q=80';
        }
        if (str_contains($text, 'cloth') || str_contains($text, 'shirt') || str_contains($text, 'baju') || str_contains($text, 'wear') || str_contains($text, 'fashion')) {
            return 'https://images.unsplash.com/photo-1521572267360-ee0c2909d518?auto=format&fit=crop&w=500&q=80';
        }

        return 'https://images.unsplash.com/photo-1526170375885-4d8ecf77b99f?auto=format&fit=crop&w=500&q=80';
    }
}
