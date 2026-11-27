// Shared ergonomics only. Native dialog owns focus containment and Escape semantics.
document.addEventListener('DOMContentLoaded',()=>{
    document.querySelectorAll('dialog.ajudae-dialog').forEach(dialog=>{
        let opener=null;
        dialog.addEventListener('beforetoggle',event=>{if(event.newState==='open')opener=document.activeElement;});
        dialog.querySelector('[data-dialog-dismiss]')?.addEventListener('click',()=>dialog.close());
        dialog.addEventListener('keydown',event=>{
            if(event.key!=='Tab')return;
            const controls=[...dialog.querySelectorAll('a[href],button:not([disabled]),input:not([disabled]),select:not([disabled]),textarea:not([disabled]),[tabindex]:not([tabindex="-1"])')]
                .filter(node=>node.tabIndex>=0 && node.getClientRects().length);
            const first=controls[0],last=controls[controls.length-1];
            if(!first)return;
            if(event.shiftKey && document.activeElement===first){event.preventDefault();last.focus();}
            else if(!event.shiftKey && document.activeElement===last){event.preventDefault();first.focus();}
        });
        dialog.addEventListener('close',()=>{
            if(opener?.isConnected && !opener.disabled && opener.getClientRects().length)opener.focus();
        });
        dialog.addEventListener('click',event=>{
            if(event.target!==dialog)return;
            const rect=dialog.getBoundingClientRect();
            if(event.clientX<rect.left || event.clientX>rect.right || event.clientY<rect.top || event.clientY>rect.bottom)dialog.close();
        });
    });
});
