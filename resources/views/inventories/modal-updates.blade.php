<div class="modal fade" id="inventoryUpdatesModal" aria-labelledby="inventoryUpdatesModalLabel" aria-hidden="true" data-focus="false">

    <div class="modal-dialog modal-dialog-centered modal-xl" id="inventoryUpdatesModalDialog">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title" id="inventoryUpdatesModalLabel">{{ __('Atualizações no inventário') }}</h3>
                <button class="btn-close" type="button" data-coreui-dismiss="modal" aria-label="Close">
                    <svg class="nav-icon">
                        <use xlink:href="{{ asset('/icons/sprites/free.svg#cil-x') }}"></use>
                    </svg>
                </button>
            </div>
            <div class="modal-body">

                <div class="mb-3">
                    <p>
                        <strong>Produto</strong>: {{ $inventory->product->name_with_attr }} <br>
                        @if ($inventory->product->sku)
                            <strong>SKU</strong>: {{ $inventory->product->sku ?? '-' }} <br>
                        @endif
                        <strong>Depósito</strong>: {{ $inventory->warehouse->name }}
                    </p>
                </div>

                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th scope="col">Autor<br>Data</th>
                            <th scope="col">Depósito Antes</th>
                            <th scope="col">Depósito Depois</th>
                            <th scope="col">Infinito Antes</th>
                            <th scope="col">Infinito Depois</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($updates as $upd)
                            <tr>
                                <td>
                                    {{$upd->author}}<br>
                                    {{$upd->created_at?$upd->created_at->format('d/m/Y H:i:s'):'-'}}
                                </td>
                                <td>{{formatQuantityField($upd->stock_before)}}</td>
                                <td>{{formatQuantityField($upd->stock_after)}}</td>
                                <td>{!!$inventory::getFieldBadge($upd->is_infinite_before)!!}</td>
                                <td>{!!$inventory::getFieldBadge($upd->is_infinite_after)!!}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5"><p>Nenhuma atualização encontrada</p></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="modal-footer">
                <button class="button btn-default" type="button" data-coreui-dismiss="modal" aria-label="Close">{{ __('Fechar') }}</button>
            </div>
        </div>
    </div>
</div>