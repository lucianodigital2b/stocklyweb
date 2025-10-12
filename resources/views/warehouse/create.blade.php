<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight mb-5 pt-4">
            {{ __('Cadastrar Depósito ') }}
        </h2>
    </x-slot>

    <div class="row">
        <form action="{{ route('warehouses.store') }}" method="post" class="col-sm-12 col-lg-12">
            @csrf

            <div class="col-12">
                <x-card-with-collapse :id="'warehouseName'" :title="''">
                    <div class="row">
                        <div class="col clearfix">
                            <div class="form-check form-switch" style="float: right;">
                                <input class="form-check-input" value="1" checked  type="checkbox" role="switch" id="status" name="status">
                                <label class="form-check-label" for="status">Ativo</label>
                              </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col">
                            <div class="mb-3">
                                <label class="form-label" for="name">Nome*</label>
                                <input class="form-control" name="name" id="name" type="text"
                                    value="{{ old('name') }}" required>
                            </div>
                        </div>
                        <div class="col">
                            <div class="mb-3">
                                <label class="form-label" for="id">ID ERP</label>
                                <input class="form-control" name="id_erp" id="id_erp" type="text"
                                    value="{{ old('id_erp') }}">
                            </div>
                        </div>
                    </div>
                </x-card-with-collapse>
            </div>

            <div class="col-12">
                <x-card-with-collapse :id="'warehouseDocks'" :title="''">
                    <div class="row">
                        <div class="col-12">
                            <div class="box-head clearfix mb-2">
                                <p class="font-medium float-left">Relação com docas</p>
                                <button class="btn btn-outline-info float-right js-add-dock-warehouse"  data-coreui-toggle="modal" data-coreui-target="#addDockModal" type="button">Adicionar Docas</button>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12">
                            <table class="table table-striped table-hover table-docks">
                                <thead>
                                    <tr>
                                        <th scope="col">Nome</th>
                                        <th scope="col">Dias e horas de processamento</th>
                                        <th scope="col">Taxa Extra</th>
                                        <th scope="col">Remover</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @isset($old['dock_id'])
                                        @foreach ($old['dock_id'] as $key => $dockItem)
                                            <x-warehouse-dock-row :dock-id="$dockItem" :dock-name="$old['dock_name'][$key]" :processing-days="$old['processing_days'][$key] ?? null" :processing-hours="$old['processing_hours'][$key] ?? null" :extra-fee-formatted="$old['extra_fee'][$key] ?? null" />
                                        @endforeach
                                    @endif
                                    <tr class="tr-empty-table @isset($old['dock_id']) hidden @endisset">
                                        <td colspan="4" class="py-3 text-center">
                                            <strong class="mr-2 inline-block align-middle">Nenhuma doca adicionada!</strong> <button type="button"
                                                class="btn btn-info js-add-dock-warehouse inline-block align-middle" data-coreui-toggle="modal"
                                                data-coreui-target="#addDockModal">Adicionar Docas</button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    
                </x-card-with-collapse>
            </div>

            <div class="mb-3">
                <a href="{{ route('deliveries.index') }}#warehouses" class=" button btn-default" type="submit">Voltar</a>

                <button style="float:right;" class="button btn-primary" type="submit">Cadastrar</button>
            </div>
        </form>
        
    </div>

    

    @push('styles')
        <style>
            .select2-container--default .select2-selection--single {
                padding: 6px 0px;
                height: auto !important;
            }
        </style>
    @endpush

    @push('scripts')
        @include('modules.warehouse.add-dock-modal')
        <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
        <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
        <script>
            window.DOCK_SEARCH_URL = '{{ route("docks.search") }}';
        </script>
        <script src="{{asset('js/warehouses.js')}}"></script>
    @endpush
</x-app-layout>