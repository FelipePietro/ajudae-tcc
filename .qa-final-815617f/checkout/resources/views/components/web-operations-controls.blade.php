<template data-operations-button>
    <x-botao-secundario as="button" type="button" :href="null" size="sm" />
</template>
<template data-operations-form>
    <form class="my-3 grid max-w-xl gap-2">
        <div data-operation-fields class="grid gap-2"></div>
        <p data-operation-feedback role="status" tabindex="-1"></p>
        <div><x-botao-primario as="button" type="submit" :href="null" size="sm" /></div>
    </form>
</template>
