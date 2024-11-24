<div class="modal fade" id="{{ $modalId }}" {{ $dataBsBackdrop }} tabindex="-1" aria-labelledby="{{ $modalId }}Label" aria-hidden="true">
    <div class="modal-dialog {{ $modalDialogClass }}">
        <div class="modal-content">
            <div class="modal-header {{ $modalHeaderClass }}">
                <h1 class="modal-title {{ $modalTitleClass }}" id="{{ $modalId }}Label">{{ $modalTitle }}</h1>
                @if ($showCloseButton)
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                @endif
            </div>
            <div class="modal-body {{ $modalBodyClass }}">
                {{ $slot }}
            </div>
            <div class="modal-footer {{ $modalFooterClass }}">
                {!! $modalFooter !!}
            </div>
        </div>
    </div>
</div>