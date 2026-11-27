import {test} from 'node:test';
import assert from 'node:assert/strict';
import {execFileSync} from 'node:child_process';
import {Script} from 'node:vm';

test('rendered Blade inline scripts parse without executing browser or network actions',()=>{
    const scripts=JSON.parse(execFileSync(process.env.AJUDAE_PHP || 'php',['tests/Frontend/rendered-dialog-scripts.php'],{encoding:'utf8',env:{...process.env,SESSION_DRIVER:'array',CACHE_STORE:'array',DB_HOST:'127.0.0.1',DB_PORT:'1',DB_URL:''}}));
    // Shared menus now live in an ES module; the create/edit forms still own inline scripts.
    for (const path of ['/ong/eventos/criar?contexto=organizador', '/ong/eventos/9/editar?contexto=organizador&jornada=independente-varios']) {
        assert.ok(scripts.some(entry => entry.path === path), path);
    }
    for(const entry of scripts) assert.doesNotThrow(()=>new Script(entry.source),entry.path);
});
