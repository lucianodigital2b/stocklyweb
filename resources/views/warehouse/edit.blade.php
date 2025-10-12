<x-app-layout>
    <x-slot name="header">
        <h2 class="mb-5 pt-4 text-xl font-semibold leading-tight text-gray-800">
            {{ __('Editar Depósito ') }}
        </h2>
    </x-slot>

    <div class="row">
        <form action="{{ route('warehouses.update', $warehouse->id) }}" method="post" class="col-sm-12 col-lg-12">
            @csrf
            @method('PUT')
            <div class="col-12">
                <x-card-with-collapse :id="'warehouseName'" :title="''">
                    <div class="row">
                        <div class="col clearfix">
                            <div class="form-check form-switch" style="float: right;">
                                <input class="form-check-input" value="1" @if ($warehouse->status) checked @endif type="checkbox" role="switch" id="status"
                                    name="status">
                                <label class="form-check-label" for="status">Ativo</label>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col">
                            <div class="mb-3">
                                <label class="form-label" for="name">Nome*</label>
                                <input class="form-control" name="name" id="name" type="text" value="{{ $warehouse->name }}" required>
                            </div>
                        </div>
                        <div class="col">
                            <div class="mb-3">
                                <label class="form-label" for="id">ID ERP</label>
                                <p class="font-medium py-1">{{ $warehouse->id_erp }}</p>
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
                                <p class="float-left font-medium">Relação com docas</p>
                                <button class="btn btn-outline-info js-add-dock-warehouse float-right" data-coreui-toggle="modal" data-coreui-target="#addDockModal"
                                    type="button">Adicionar Docas</button>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12">
                            <table class="table-striped table-hover table-docks table">
                                <thead>
                                    <tr>
                                        <th scope="col">Nome</th>
                                        <th scope="col">Dias e horas de processamento</th>
                                        <th scope="col">Taxa Extra</th>
                                        <th scope="col">Remover</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if ($warehouse->docksRelation->count())
                                        @foreach ($warehouse->docksRelation as $key => $dock)
                                            @if ($dock->dock)
                                                <x-warehouse-dock-row 
                                                    :dock-id="$dock->dock->id" 
                                                    :dock-name="$dock->dock->name"
                                                    :processing-days="$dock->processing_days??null" 
                                                    :processing-hours="$dock->processing_hours??null" 
                                                    :extra-fee-formatted="$dock->extra_fee??null"
                                                    />
                                                
                                            @endif
                                        @endforeach
                                    @else
                                    <tr class="tr-empty-table @if($warehouse->docksRelation->count()) hidden @endif ">
                                        <td colspan="4" class="text-center py-3">
                                            <strong class="mr-2 inline-block align-middle">Nenhuma doca adicionada!</strong> <button  type="button" class="btn btn-info inline-block align-middle js-add-dock-warehouse" data-coreui-toggle="modal" data-coreui-target="#addDockModal">Adicionar Docas</button>
                                        </td>
                                    </tr>
                                    @endif

                                    </tbody>
                                </table>
                            </div>
                        </div>


                    </x-card-with-collapse>
                </div>

                <div class="mb-3">
                    <a href="{{ route('deliveries.index') }}#warehouses" class="button btn-default" type="submit">Voltar</a>

                    <button style="float:right;" class="button btn-primary" type="submit">Salvar</button>
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
                window.DOCK_SEARCH_URL = '{{ route('docks.search') }}';
            </script>
            <script src="{{ asset('js/warehouses.js') }}"></script>
        @endpush
    </x-app-layout>
