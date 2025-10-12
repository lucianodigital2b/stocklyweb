<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight mb-5 pt-4">
            {{ __('Cadastrar Inventário') }}
        </h2>
    </x-slot>

    <div class="row">
        <form action="{{ route('inventories.store') }}" method="post" class="col-sm-12 col-lg-12" id="inventoryForm">
            @csrf
            <div class="row">
                <div class="col-12">

                    <x-card-with-collapse :id="'inventoryRelations'" :title="''">
                        <div class="row clearfix mb-3">
                            <div class="col-12 clearfix">
                                <div class="form-check form-switch form-switch-lg p-0">
                                    <label class="form-label block" for="status">Status</label>
                                    <input class="form-check-input m-0 !w-10" value="1" checked  type="checkbox" role="switch" id="status" name="status">
                                  </div>
                            </div>
                        </div>
                        <div class="row ">
                            <div class="col">
                                <div class="mb-3">
                                    <label class="form-label" for="name">Produto*</label>
                                    <input type="hidden" name="product_id" id="product_id">
                                </div>
                            </div>
                            <div class="col">
                                <div class="">
                                    <label class="form-label" for="id">Depósito*</label>
                                    <input type="hidden" name="warehouse_id" id="warehouse_id">
                                </div>
                            </div>
                        </div>
                    </x-card-with-collapse>

                </div>
            </div>	

            <div class="row">
                <div class="col-12">

                    <x-card-with-collapse :id="'inventoryQuantity'" :title="''">
                        <div class="row clearfix mb-3">

                            <div class="col-6">
                                <label class="" for="stock">Quantidade</label>
                                <input type="text" name="stock" class="form-control mask-int" id="stock" value="{{old('stock')}}">
                            </div>
                            <div class="col-6">
                                <div class="form-check form-switch form-switch-lg p-0">
                                    <label class="form-label block" for="is_infinite">Depósito Infinito</label>
                                    <input class="form-check-input m-0 !w-10" value="1"  type="checkbox" role="switch" id="is_infinite" name="is_infinite" >
                                  </div>
                            </div>

                        </div>
                    </x-card-with-collapse>

                </div>
            </div>	

            <div class="mb-3">
                <a href="{{ route('inventories.index') }}" class=" button btn-default" type="submit">Voltar</a>

                <button style="float:right;" class="button btn-primary" type="submit">Salvar</button>
            </div>
        </form>
    </div>


    @push('styles')
    <style>
        #inventoryForm .select2-container .select2-choice {
            padding: 0.375rem 25px 0.375rem 0.75rem;
            height: auto !important;
        }
        #inventoryForm .select2-container .select2-choice .select2-arrow b {
            margin-top: 5px;
        }
    </style>

    @endpush
    @push('scripts')

    <link data-require="select2@*" data-semver="3.5.1" rel="stylesheet" href="//cdn.jsdelivr.net/select2/3.4.5/select2.css" />
    <script data-require="select2@*" data-semver="3.5.1" src="//cdn.jsdelivr.net/select2/3.5.1/select2.js"></script>

    <script>
        window.PRODUCT_SEARCH_URL = "{{ route('products.search-select') }}";
        window.WAREHOUSE_SEARCH_URL = "{{ route('warehouses.search-select') }}";

        $(function() {
            $('.mask-int').mask('#.##0', {
                reverse: true
            });
        });

        $('#product_id').select2({
            selectOnBlur: true,
            allowClear: true,
            placeholder: 'Escolha o produto',
            minimumInputLength: 3,
            ajax: {
                method: 'get',
                url: window.PRODUCT_SEARCH_URL,
                data: function (term, page, b, c) {
                    return {
                        limit: 10,
                        q: term,
                        page: page
                    };
                },
                dataType: 'json',
                results: function results(data, page) {
                    console.log( data.total_count > (data.page * 10) );
                    return {
                        results: data.items,
                        more:data.total_count > (data.page * 10),
                    };
                }
            }
        });

        $('#warehouse_id').select2({
            selectOnBlur: true,
            allowClear: true,
            placeholder: 'Escolha o produto',
            minimumInputLength: 3,
            ajax: {
                method: 'get',
                url: window.WAREHOUSE_SEARCH_URL,
                data: function (term, page, b, c) {
                    return {
                        limit: 10,
                        q: term,
                        page: page
                    };
                },
                dataType: 'json',
                results: function results(data, page) {
                    console.log( data.total_count > (data.page * 10) );
                    return {
                        results: data.items,
                        more:data.total_count > (data.page * 10),
                    };
                }
            }
        });

        $('#product_id').data('select2').$container.addClass('form-control');
    </script>
@endpush

</x-app-layout>