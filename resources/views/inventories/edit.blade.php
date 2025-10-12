<x-app-layout>
    <x-slot name="header">
        <div class="mb-3">
            <a href="{{ route('inventories.index') }}" class=" button btn-default">Voltar</a>
            <a href="{{ route('inventories.create') }}" class="button btn-success">Novo</a>
            
            <form onsubmit="return confirm('Tem certeza que deseja prosseguir?');" action="{{ route('inventories.destroy', $inventory->id) }}" method="post" style="display: inline; border: none; background: transparent;float:right">
                <button type="submit" class="button btn-danger m-0">
                  {{ __('Remover') }}
                </button>
                
                @method('DELETE')
                @csrf
            </form>
        </div>
        <h2 class="font-semibold text-xl text-gray-800 leading-tight mb-5 pt-4">
            {{ __('Editar Inventário') }}
        </h2>
    </x-slot>

    <div class="row">
        <form action="{{ route('inventories.update', $inventory->id) }}" method="post" class="col-sm-12 col-lg-12" id="inventoryForm">
            @csrf
            @method('PUT')
            <div class="row">
                <div class="col-12">

                    <x-card-with-collapse :id="'inventoryRelations'" :title="''">
                        <div class="row clearfix mb-3">
                            <div class="col-12 clearfix">
                                <div class="form-check form-switch form-switch-lg p-0">
                                    <label class="form-label block" for="status">Status</label>
                                    <input class="form-check-input m-0 !w-10" value="1" {{$inventory->status?'checked':''}} type="checkbox" role="switch" id="status" name="status" >
                                  </div>
                            </div>
                        </div>
                        <div class="row ">
                            <div class="col">
                                <div class="mb-3">
                                    <label class="form-label" for="name">Produto*</label>
                                    <input type="text" class="form-control" readonly="readonly" name="product_str" id="product_str" value="{{$inventory->product->id}}{{$inventory->product->sku?(' - '.$inventory->product->sku):''}} - {{$inventory->product->name_with_attr}}">
                                    <input type="hidden" name="product_id" id="product_id" value="{{$inventory->product->id}}">
                                </div>
                            </div>
                            <div class="col">
                                <div class="">
                                    <label class="form-label" for="id">Depósito*</label>
                                    <input type="text" class="form-control" readonly="readonly" name="warehouse_str" id="warehouse_str" value="{{$inventory->warehouse->id}}{{$inventory->warehouse->id_erp?(' - '.$inventory->warehouse->sku):''}} - {{$inventory->warehouse->name}}">
                                    <input type="hidden" name="warehouse_id" id="warehouse_id" value="{{$inventory->warehouse->id}}">
                                </div>
                            </div>
                        </div>
                    </x-card-with-collapse>

                </div>
            </div>	

            <div class="row">
                <div class="col-12">

                    <x-card-with-collapse :id="'inventoryQuantity'" :title="''">
                        <div class="row clearfix">

                            <div class="col-6">
                                <label class="" for="stock">Quantidade</label>
                                <input type="text" name="stock" class="form-control mask-int" id="stock" value="{{$inventory->stock}}">
                            </div>
                            <div class="col-6">
                                <div class="form-check form-switch form-switch-lg p-0">
                                    <label class="form-label block" for="is_infinite">Depósito Infinito</label>
                                    <input class="form-check-input m-0 !w-10" value="1" {{$inventory->is_infinite?'checked':''}}  type="checkbox" role="switch" id="is_infinite" name="is_infinite">
                                  </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12">
                                <label class="form-label block" for="">&nbsp;</label>
                                <button class="btn btn-outline-dark js-show-updates-modal" type="button" data-route="{{route('inventories.updates-modal', $inventory->id)}}">{{ __('Atualizações de inventário') }}</button>
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

    <style>
           #inventoryUpdatesModal .table td{
                width:auto !important;
                padding:.5rem !important;
            }
            #inventoryUpdatesModal table, 
            .table-inventory {
                font-size: 0.875rem;
            }
    </style>

    @push('scripts')

    <script>
        $(function() {
            $('.mask-int').mask('#.##0', {
                reverse: true
            });

            $('.js-show-updates-modal').off('click').on('click', function(e) {
                e.preventDefault();
                if(!$(this).data('route')) return;

                showLoader();
                $.ajax({
                    url: $(this).data('route'),
                    type: "GET",
                    dataType: 'json',
                    error: function(request, status, error) {
                        ajaxError(request);
                    },
                    success: function(response) {
                        if(!response.html){
                            alert('Erro interno no servidor. Tente novamente mais tarde.');
                            console.log(response);
                            return;
                        }

                        $('#inventoryUpdatesModal').remove();
                        $('body').append(response.html);
                        let modalUpdates = new coreui.Modal($('#inventoryUpdatesModal'));
                        modalUpdates.show();
                    },
                    complete: function() {
                        hideLoader();
                    }
                });
            });
        });

    </script>
@endpush

</x-app-layout>