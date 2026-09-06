<nav class="navbar">

    <a href="/" class="navbar-brand">
        <span class="brand-aju">Ajud</span>
        <span class="brand-dae">ae</span>
    </a>


    <button
        type="button"
        id="navbar-toggle"
        class="navbar-toggle"
        aria-label="Abrir menu"
        aria-expanded="false"
    >
        ☰
    </button>


    <ul
        id="navbar-links"
        class="navbar-links"
    >

        <li>
            <a href="/feed">
                Feed
            </a>
        </li>

        <li>
            <a href="/minhas-candidaturas">
                Candidaturas
            </a>
        </li>

        <li>
            <a href="/perfil">
                Meu Perfil
            </a>
        </li>

        <li>
            <a href="/ranking">
                Ranking
            </a>
        </li>

        <li>
            <a
                href="/notificacoes"
                class="btn-icone"
            >
                🔔
            </a>
        </li>

        <li>
            <a
                href="/perfil"
                class="avatar"
            >
                LP
            </a>
        </li>

    </ul>

</nav>


<script>

    document.addEventListener('DOMContentLoaded', () => {

        const toggle =
            document.querySelector('#navbar-toggle');

        const links =
            document.querySelector('#navbar-links');


        toggle?.addEventListener('click', () => {

            links.classList.toggle(
                'navbar-links-aberto'
            );


            const aberto =
                links.classList.contains(
                    'navbar-links-aberto'
                );


            toggle.setAttribute(
                'aria-expanded',
                aberto
            );


            toggle.textContent =
                aberto ? '✕' : '☰';

        });

    });

</script>