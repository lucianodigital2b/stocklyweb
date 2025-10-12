<x-app-layout>
    <x-slot name="header">
        <div class="mb-3">
            <a href="{{ route('deliveries.index') }}#docks" class=" button btn-default" type="submit">Voltar</a>
            
            <form onsubmit="return confirm('Tem certeza que deseja prosseguir?');"  id="form-{{ $dock->id }}" action="{{ route('docks.destroy', $dock->id) }}" method="post" style="display: inline; border: none; background: transparent;float:right">
                <button type="submit" class="button btn-danger">
                  {{ __('Remover') }}
                </button>
                
                @method('DELETE')
                @csrf
            </form>
        </div>
        <h2 class="font-semibold text-xl text-gray-800 leading-tight mb-5 pt-4">
            {{ __('Editar Doca') }}
        </h2>
    </x-slot>

    <div class="row">
        <form action="{{ route('docks.update', $dock->id) }}" method="post" class="col-sm-12 col-lg-12">
            @csrf
            @method('PUT')
            <div class="col-12">
                <x-card-with-collapse :id="'dockName'" :title="''">
                    <div class="row">
                        <div class="col clearfix">
                            <div class="form-check form-switch" style="float: right;">
                                <input class="form-check-input" value="1" @if ($dock->status) checked @endif type="checkbox" role="switch" id="status" name="status">
                                <label class="form-check-label" for="status">Ativo</label>
                              </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col">
                            <div class="mb-3">
                                <label class="form-label" for="name">Nome*</label>
                                <input class="form-control" name="name" id="name" type="text"
                                    value="{{ $dock->name }}" required>
                            </div>
                        </div>
                    </div>
                            </div>
                        </div>
                    </div>
                </x-card-with-collapse>
            </div>


            <div class="col-12">
                <x-card-with-collapse :id="'politicasEnvio'" :title="''">
                    <div class="mb-3  ">
                        <div class="row g-3">
                            <div class="col">
                                <label class="form-check-label" for="flexCheckDefault">
                                    <b>Políticas de envio e canais de venda</b>
                                    <br>
                                    Selecione políticas de envio e políticas de vendas cadastradas em sua loja
                                </label>
                            </div>
                            <div class="col">

                                <div>
                                    <label class="form-label" for="description">Políticas de envios
                                        associadas</label>
                                    <select class="products-multiple form-control" name="shipping_policies[]"
                                        multiple="multiple">
                                        @foreach ($shippings as $shipping)
                                            <option value="{{$shipping->id}}"
                                                @isset ($shippingPoliciesDock[$shipping->id]) selected @endif> {{$shipping->name}}</option>
                                        @endforeach
                                    </select>

                                </div>
                                <br>
                                <div>
                                    <label class="form-label" for="description">Canais de vendas</label>
                                    <select class="products-multiple form-control" name="store_fronts[]" multiple="multiple">
                                        @foreach ($apps as $app)
                                            <option value="{{$app->id}}" @isset ($appsDock[$app->id]) selected @endif>
                                                {{$app->name}}</option>
                                        @endforeach
                                    </select>
                                </div>

                            </div>
                        </div>
                    </div>
                </x-card-with-collapse>
            </div>

            <div class="col-12">
                <x-card-with-collapse :id="'prioridadesAdicional'" :title="''">
                    <div class="mb-3  ">
                        <div class="row g-3">
                            <div class="col">
                                <label class="form-check-label" for="flexCheckDefault">
                                    <b>Prioridade e adicional de tempo</b>
                                </label>
                            </div>
                            <div class="col">
                                <table>
                                    <tbody>
                                        <tr>
                                            <h3>Tempo de custo</h3>
                                            <br>
                                            <td>
                                                <div class="col-md-11">
                                                    <label for="monday" class="form-label" id="service_time_days"><small>Dias</small></label>
                                                    <input type="number" class="form-control" value="{{$dock->service_time_days}}" name="service_time_days">
                                                </div>
                                            </td>
                                            <td>
                                                <div class="col">
                                                    <label for="service_time_hours" class="form-label"><small>Horas e minutos</small></label>
                                                    <input type="time" class="form-control" id="service_time_hours" name="service_time_hours" value="{{$dock->service_time_hours}}">
                                                </div>
                                            </td>

                                        </tr>
                                    </tbody>
                                </table>
                                <br>
                                <table>
                                    <tbody>
                                        <tr>
                                            <h3>Overhead de tempo de custo</h3>
                                            <br>
                                            <td>
                                                <div class="col-md-11">
                                                    <label for="overhead_days" class="form-label"><small>Dias</small></label>
                                                    <input type="number" class="form-control" value="{{$dock->overhead_days}}" name="overhead_days" id="overhead_days">
                                                </div>
                                            </td>
                                            <td>
                                                <div class="col">
                                                    <label for="overhead_hours" class="form-label"><small>Horas e minutos</small></label>
                                                    <input type="time" class="form-control" name="overhead_hours" id="overhead_hours" value="{{$dock->overhead_hours}}">
                                                </div>
                                            </td>

                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    {{-- <br>
                    <hr>
                    <br>
                    <div class="mb-3  ">
                        <div class="row g-3">
                            <div class="col">
                                <label class="form-check-label" for="flexCheckDefault">
                                    <b>Prioridade e adicional de tempo</b>
                                </label>
                            </div>
                            <div class="col">
                                <label for="customRange1" class="form-label">Prioridade</label>
                                <input type="range" class="form-range" min="0" max="10" id="customRange2">
                            </div>
                        </div>
                    </div> --}}
                </x-card-with-collapse>
            </div>

            <div class="col-12">
                <x-card-with-collapse :id="'endereco'" :title="''">
                    <div class="mb-3  ">
                        <div class="row g-3">
                            <div class="col">
                                <label class="form-check-label" for="flexCheckDefault">
                                    <b>Endereço</b>
                                    <br>
                                    Estas informações referem-se ao local desta doca, de onde os produtos são enviados.
                                </label>
                            </div>
                            <div class="col">

                                <div class="row">
                                    <div class="col-6 col-md-4">
                                        <div class="mb-3">
                                            <label class="form-label" for="country">Pais</label>
                                            <input class="form-control" name="country" id="country" type="text" value="{{$dock->country}}">
                                        </div>
                                    </div>
                                    <div class="col-md-8">
                                        <label class="form-label" for="postal_code">Código Postal</label>
                                        <input class="form-control" name="postal_code" id="postal_code" type="text" value="{{$dock->postal_code}}">
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-8">
                                        <label class="form-label" for="address">Rua</label>
                                        <input class="form-control" name="address" id="address" type="text" value="{{$dock->address}}">
                                    </div>
                                    <div class="col-6 col-md-4">
                                        <label class="form-label" for="address_number">Número</label>
                                        <input class="form-control" name="address_number" id="address_number" type="text" value="{{$dock->address_number}}">
                                    </div>
                                </div>


                                <div class="row">
                                    <div class="col-6">
                                        <label class="form-label" for="address_complement">Complemento</label>
                                        <input class="form-control" name="address_complement" id="address_complement" type="text" value="{{$dock->address_complement}}">
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label" for="neighborhood">Bairro</label>
                                        <input class="form-control" name="neighborhood" id="neighborhood" type="text" value="{{$dock->neighborhood}}">
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-6 col-md-4">
                                        <label class="form-label" for="state">Estado</label>
                                        <input class="form-control" name="state" id="state" type="text" value="{{$dock->state}}">
                                    </div>
                                    <div class="col-md-8">
                                        <label class="form-label" for="city">Cidade</label>
                                        <input class="form-control" name="city" id="city" type="text" value="{{$dock->city}}">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </x-card-with-collapse>
            </div>

            <div class="mb-3">
                <a href="{{ route('deliveries.index') }}#docks" class=" button btn-default" type="submit">Voltar</a>

                <button style="float:right;" class="button btn-primary" type="submit">Salvar</button>
            </div>
        </form>
    </div>
    @push('scripts')
        <script src="{{ asset('js/custom-file-input.js') }}"></script>
        <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
        <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

        <script>
            $(document).ready(function() {
                $('.products-multiple').select2();
            });
        </script>
    @endpush
    <script>
        $(document).ready(function() {

            $('#btn-envio').click(function() {
                event.preventDefault();
                $('#time-type').val('shipping');
                $('#btn-envio').attr('style', 'border: 2px solid #3c4b64;');
                $('#btn-coleta').attr('style', 'border: 0px solid #3c4b64;');
            });

            $('#btn-coleta').click(function() {
                event.preventDefault();
                $('#time-type').val('collect');
                $('#btn-coleta').attr('style', 'border: 2px solid #3c4b64;');
                $('#btn-envio').attr('style', 'border: 0px solid #3c4b64;');
            });
        });
    </script>
</x-app-layout>
