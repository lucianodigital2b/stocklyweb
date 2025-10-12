<style>
    #addDockModal .table td{
        width: initial;
    }
    #addDockModal label {
        padding: 7px 20px 7px 7px;
    }
    #addDockModal .select2{
        max-width: 700px;
        width: 50%;
        vertical-align: middle;
        display: inline-block;
        text-align: left;
    }
    #addDockModal .select2-selection__arrow{
        height: 100%;
        top: 3px;
        width: 30px;
    }
</style>
<div class="modal fade" id="addDockModal" aria-labelledby="addDockModalLabel" aria-hidden="true" data-focus="false"
    style="overflow:hidden;" style="overflow:hidden;">

    <div class="modal-dialog modal-dialog-centered modal-xl" id="addDockModalDialog">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title" id="addDockModalLabel">{{ __('Adicionar doca') }}</h3>
                <button class="btn-close" type="button" data-coreui-dismiss="modal" aria-label="Close">
                    <svg class="nav-icon">
                        <use xlink:href="{{ asset('/icons/sprites/free.svg#cil-x') }}"></use>
                    </svg>
                </button>
            </div>
            <div class="modal-body">

              
                <div class="mb-3 text-right">
                    <label for="selectDock" class="align-middle inline-block font-medium">Adicionar docas</label>
                    <input type="hidden" name="na" id="selectDock">
                    <div class="inner ">
                    </div>
                </div>

                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th scope="col">Nome</th>
                            <th scope="col">Dias e horas de processamento</th>
                            <th scope="col">Taxa Extra</th>
                            <th scope="col">Remover</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
            <div class="modal-footer">
                <button class="button btn-primary js-chosen-dock" type="button" data-coreui-dismiss="modal" aria-label="Close">{{ __('Adicionar') }}</button>
            </div>
        </div>
    </div>
</div>
