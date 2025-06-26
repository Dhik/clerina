<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Product Details</h3>
                <div class="card-tools">
                    <div class="input-group input-group-sm" style="width: 250px;">
                        <select id="quickFilter" class="form-control">
                            <option value="">All Quadrants</option>
                            <option value="Stars">⭐ Stars</option>
                            <option value="Cash Cows">💰 Cash Cows</option>
                            <option value="Question Marks">❓ Question Marks</option>
                            <option value="Dogs">❌ Dogs</option>
                        </select>
                        <div class="input-group-append">
                            <button type="button" class="btn btn-default" data-toggle="modal" data-target="#advancedFilterModal">
                                <i class="fas fa-cog"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="productsTable" class="table table-bordered table-striped table-hover">
                        <thead class="thead-dark">
                            <tr>
                                <th>Quadrant</th>
                                <th>Product Code</th>
                                <th>Product Name</th>
                                <th>SKU</th>
                                <th>Traffic</th>
                                <th>Buyers</th>
                                <th>Conv. Rate</th>
                                <th>Benchmark</th>
                                <th>Price</th>
                                <th>Revenue</th>
                                <th>ROAS</th>
                                <th>Stock</th>
                                <th>Performance</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($processedProducts as $product)
                            <tr data-quadrant="{{ $product['quadrant'] }}" class="clickable-row" data-sku="{{ $product['sku'] }}">
                                <td>
                                    <span class="badge" style="background-color: {{ $product['quadrant_color'] }}; color: {{ $product['quadrant'] === 'Cash Cows' ? 'black' : 'white' }};">
                                        @if($product['quadrant'] === 'Stars')⭐@elseif($product['quadrant'] === 'Cash Cows')💰@elseif($product['quadrant'] === 'Question Marks')❓@else❌@endif
                                        {{ $product['quadrant'] }}
                                    </span>
                                </td>
                                <td class="product-code-cell">{{ $product['kode_produk'] }}</td>
                                <td>
                                    <div class="product-name">{{ Str::limit($product['nama_produk'], 30) }}</div>
                                    @if(strlen($product['nama_produk']) > 30)
                                        <small class="text-muted" title="{{ $product['nama_produk'] }}">...</small>
                                    @endif
                                </td>
                                <td>{{ $product['sku'] ?: '-' }}</td>
                                <td class="text-right">{{ number_format($product['visitor']) }}</td>
                                <td class="text-right">{{ number_format($product['jumlah_pembeli']) }}</td>
                                <td class="text-center">
                                    <span class="badge badge-{{ $product['conversion_rate'] >= $product['benchmark_conversion'] ? 'success' : 'warning' }}">
                                        {{ $product['conversion_rate'] }}%
                                    </span>
                                </td>
                                <td class="text-center">{{ $product['benchmark_conversion'] }}%</td>
                                <td class="text-right">Rp {{ number_format($product['harga']) }}</td>
                                <td class="text-right">
                                    <strong>Rp {{ number_format($product['sales']) }}</strong>
                                </td>
                                <td class="text-center">
                                    @if($product['roas'] > 0)
                                        <span class="badge badge-{{ $product['roas'] >= 3 ? 'success' : ($product['roas'] >= 1 ? 'warning' : 'danger') }}">
                                            {{ $product['roas'] }}x
                                        </span>
                                    @else
                                        <span class="badge badge-secondary">N/A</span>
                                    @endif
                                </td>
                                <td class="text-right">{{ number_format($product['stock']) }}</td>
                                <td class="text-center">
                                    @php
                                        $performance = (($product['conversion_rate'] >= $product['benchmark_conversion'] ? 25 : 0) + 
                                                      ($product['roas'] >= 3 ? 25 : ($product['roas'] >= 1 ? 15 : 0)) + 
                                                      ($product['visitor'] >= $medianTraffic ? 25 : 10) + 
                                                      (($product['stock'] > 0 && $product['qty_sold']/$product['stock'] >= 1) ? 25 : 15));
                                    @endphp
                                    <div class="progress" style="height: 20px;">
                                        <div class="progress-bar bg-{{ $performance >= 75 ? 'success' : ($performance >= 50 ? 'warning' : 'danger') }}" 
                                             role="progressbar" style="width: {{ $performance }}%">
                                            {{ $performance }}
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>