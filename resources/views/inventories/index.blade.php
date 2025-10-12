<x-app-layout>

    <h3 class="mb-5 text-xl font-semibold">Inventário</h3>

    <div class="row">
        <div class="col-sm-12 col-lg-12 bg-white p-3">

            <nav class="navbar navbar-light bg-light">
                <div class="container-fluid">
                    <form class="d-flex">
                        <input class="form-control me-2" type="search" placeholder="Pesquise por inventário" aria-label="Search" name="q" value="{{$search}}">
                        <button class="btn btn-outline-primary" type="submit">Buscar</button>

                        @if (!empty($search))
                            <div class="col-auto ml-3">
                                <a href="{{ route('inventories.index') }}" class="btn btn-outline-warning" >Limpar pesquisa</a>
                            </div>
                        @endif
                    </form>
                    <form class="d-flex">
                        <a href="{{ route('inventories.create') }}" class="button btn-primary">Criar Inventário</a>
                    </form>
                </div>
            </nav>
            <div class="table-responsive table-inventory min-h-[200px]">
                <table class="table-striped table">
                    <thead>
                        <tr>
                            <th>
                                Produto<br>
                                SKU
                            </th>
                            <th>Depósito</th>
                            <th>Total</th>
                            <th>
                                Reservados<br>
                                Envios
                            </th>
                            <th>Disponível</th>
                            <th>Atualizar Total</th>
                            <th></th>
                            <th class="p-0"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($inventories as $inventory)
                            <tr data-id="{{ $inventory->id }}">
                                <td class="max-w-[275px]">
                                    <span class="one-line-only" data-coreui-toggle="tooltip" data-coreui-placement="top"
                                        title="{{ $inventory->product->name_with_attr }}">{{ $inventory->product->name_with_attr }}</span>
                                    {{ $inventory->product->sku ?? '-' }}
                                </td>
                                <td>{{ $inventory->warehouse->name }}</td>
                                <td class=""> 
                                    <a href="#" class="js-total-stock js-show-updates-modal fw-medium underline" data-route="{{route('inventories.updates-modal', $inventory->id)}}">{{ formatQuantityField($inventory->stock) }}</a>
                                    <span class="js-total-stock-change text-danger block hidden"></span>
                                </td>
                                <td>
                                    {{ formatQuantityField($inventory->stock_reserved) }}<br>
                                    {{ formatQuantityField($inventory->stock_shipping) }}
                                </td>
                                <td class=""> 
                                    <span class="js-stock-available">{{ formatQuantityField($inventory->stock_available) }}</span>
                                    <span class="js-stock-available-change text-danger block hidden"></span>
                                </td>
                                </td>
                                <td>
                                    <input type="text" class="mask-int js-update-stock form-control max-w-[100px]" placeholder="{{ formatQuantityField($inventory->stock) }}">
                                </td>
                                <td>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input js-switch-inventory" value="1" @if ($inventory->status) checked @endif type="checkbox" role="switch" name="status">
                                        <label class="form-check-label" for="status">Status</label>
                                    </div>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input js-switch-inventory" value="1" @if ($inventory->is_infinite) checked @endif type="checkbox" role="switch" name="is_infinite">
                                        <label class="form-check-label" for="status">Infinito</label>
                                    </div>
                                </td>
                                <td class="p-0">
                                    <div class="dropdown">
                                        <button class="btn btn-transparent p-0" type="button" data-coreui-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                            <svg class="icon">
                                                <use xlink:href="{{ asset('/icons/sprites/free.svg') }}#cil-options"></use>
                                            </svg>
                                        </button>
                                        <div class="dropdown-menu dropdown-menu-end p-0">
                                            <a class="dropdown-item button rounded-0 fw-medium m-0 p-2" href="{{ route('inventories.edit', $inventory->id) }}">
                                                {{ __('Editar') }}
                                            </a>
                                            <a href="{{ route('inventories.delete', $inventory->id) }}" class="dropdown-item button rounded-0 fw-medium text-danger m-0 p-2" onclick="return confirm('Tem certeza que deseja prosseguir?');">
                                                {{ __('Remover') }}
                                            </a>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center"><span class="pr-4">Nenhum inventário cadastrado!</span><a href="{{ route('inventories.create') }}"
                                        class="button btn-primary">Criar Inventário</a></td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="row navigation__wrapper">
                {{ $inventories->appends(request()->query())->links() }}
            </div>
        </div>
    </div>

    <div class="save-bar fixed bottom-5 right-5 z-50 hidden shadow-lg">
        <div class="save-bar__inner">
            <button class="button btn-success js-save-index-update m-0" type="button">
                <span>Salvar alterações</span>
                <svg class="icon">
                    <use xlink:href="{{ asset('/icons/sprites/free.svg') }}#cil-save"></use>
                </svg>
            </button>
        </div>
    </div>

    @push('styles')
        <style>
            #inventoryUpdatesModal .table td{
                width:auto !important;
                padding:.5rem !important;
            }
            #inventoryUpdatesModal table, 
            .table-inventory {
                font-size: 0.875rem;
            }

            .table-inventory tbody td {
                line-height: 1.3;
            }

            .one-line-only {
                margin: 0;
                text-overflow: ellipsis;
                overflow: hidden;
                display: -webkit-box;
                -webkit-line-clamp: 1;
                -webkit-box-orient: vertical;
            }

        </style>
    @endpush

    @push('scripts')
    
        <script>
            function clearPageState() {
                window.onbeforeunload = s => null;
            }

            function warnPageState() {
                window.onbeforeunload = s => true;
            }

            function ajaxError(response) {
                let errorMessage = 'Erro interno no servidor. Tente novamente.';
                try {
                    let response = request.responseJSON;

                    if (response.message) {
                        errorMessage = response.message;
                    }
                } catch (error) {
                    console.log(error);
                }

                window.alert(errorMessage);
            }

            function sendChanges() {
                $('.js-save-index-update').off('click').on('click', function(e) {
                    e.preventDefault();

                    if (!Object.keys(window.INVENTORY_CHANGES).length) {
                        return;
                    }
                    showLoader();
                    clearPageState();
                    $.ajax({
                        url: "{{ route('inventories.update-index') }}",
                        type: "POST",
                        dataType: 'json',
                        data: {
                            _token: "{{ csrf_token() }}",
                            changes: Object.values(window.INVENTORY_CHANGES)
                        },
                        error: function(request, status, error) {
                            warnPageState();
                            ajaxError(request);
                        },
                        success: function() {
                            window.location.reload();
                        },
                        complete: function() {
                            hideLoader();
                        }
                    });
                });
            }
        </script>
        <script src="{{ asset('js/inventory-index.js') }}"></script>
    @endpush

</x-app-layout>
