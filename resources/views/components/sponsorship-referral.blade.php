@php($inputCode = request()->query('padrinho'))
<div class="account-note account-referral">
    <label for="signup-referral" class="account-field">
        Código de indicação de padrinho (opcional)
        <input id="signup-referral" data-referral-code value="{{ is_string($inputCode) && preg_match('/^[a-z0-9-]{1,25}$/iD', $inputCode) ? $inputCode : '' }}" maxlength="25" autocomplete="off" spellcheck="false" aria-describedby="signup-referral-message signup-referral-help">
    </label>
    <p id="signup-referral-message" role="status" data-referral-message></p>
    <p id="signup-referral-help" class="account-hint">O código indica quem receberá seu pedido após o cadastro. A análise do cadastro e dos documentos continua com a administração.</p>
    <label class="account-consent" hidden><input type="checkbox" data-referral-consent> Compartilhar as causas, habilidades e recursos selecionados com a pessoa indicada.</label>
</div>
