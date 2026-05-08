(() => {

    // get elemets li.dropdown vanila javascript
    var dropdowns = document.querySelectorAll('a.link-sidebar-tree');
    // click event
    dropdowns.forEach((dropdown) => {
        dropdown.addEventListener('click', (e) => {
            let id = dropdown.getAttribute('data-id');
            // find ul child
            let ul = document.querySelector('ul#'+id);

            // add class open
            if(ul.classList.contains('open')){
                dropdown.classList.remove('open');
                ul.classList.remove('open');
            } else{
                ul.classList.add('open');
                dropdown.classList.add('open');
            }
        });
    }
    );

    // on clik button data-type=editip on vanila js
    var editip = document.querySelectorAll('button[data-type=editip]');
    editip.forEach((btn) => {
        btn.addEventListener('click', (e) => {
            // get data id 
            let id = btn.getAttribute('data-id');

            // add param _get to url
            let url = window.location.href;
            let param = url.split('?');
            
            let params = param[1].split('&');
            // search param _get
            let search_id = params.find((s) => {
                return s.indexOf('id_record') > -1;
            });

            if (!search_id) {
               // create param _get
                search_id = 'id_record='+id;
                params.push(search_id);
            } else {
                // replace param _get
                let index = params.indexOf(search_id);
                params[index] = 'id_record='+id;
            }

            // join param _get
            params = params.join('&');

            // join
            url = param[0]+'?'+params;
            
            // redirect url
            window.location.href = url;

        });
    });

    // on clik button data-type=cancelip on vanila js
    var cancelip = document.querySelectorAll('button[data-type=cancelip]');
    cancelip.forEach((btn) => {
        btn.addEventListener('click', (e) => {
            // get data id 
            let id = btn.getAttribute('data-id');

            // add param _get to url
            let url = window.location.href;
            let param = url.split('?');
            
            let params = param[1].split('&');
            // search param _get
            let search_id = params.find((s) => {
                return s.indexOf('id_record') > -1;
            });

            if (search_id) {
               // remove param _get
                let index = params.indexOf(search_id);
                params.splice(index, 1);
            } 

            // join param _get
            params = params.join('&');

            // join
            url = param[0]+'?'+params;
            
            // redirect url
            window.location.href = url;
        });
    });
    
    // on click button data-type=deleteip on vanila js
    var deleteip = document.querySelectorAll('button[data-type=deleteip]');
    deleteip.forEach((btn) => {
        btn.addEventListener('click', (e) => {
            // show modal
            const modal = document.getElementById("modal_delete");

            // open modal
            modal.classList.add("is-visible");

            // get data id
            let id = btn.getAttribute('data-id');

            // add to input name=iddelete
            let input = modal.querySelector('input[name=iddelete]');
            input.value = id;

        });
    });

    // on click button data-type=saveip on vanila js
    var saveip = document.querySelectorAll('button[data-type=saveip]');
    saveip.forEach((btn) => {
        btn.addEventListener('click', (e) => {
            // get data id
            let id = btn.getAttribute('data-id');
            // get data act
            let act = btn.getAttribute('data-act');

            // get data key
            let inputKey = document.querySelector('input[name=key]');
            inputKey.value = id;
            // get data act
            let inputAct = document.querySelector('input[name=act]');
            inputAct.value = act;

            // submit form
            document.querySelector('form#form-ip').submit();
        });
    });

    // event click collapse
    var collapse = document.querySelectorAll('.panel-group .panel-heading');
    collapse.forEach((el) => {
        el.addEventListener('click', (e) => {
            // get id from href
            let id = el.getAttribute('data-target');
            // get element by id
            let element = document.querySelector(id);
            // get icon from element
            let icon = el.querySelector('i.indicator-icon');
            // add class open
            if(element.classList.contains('collapsed')){
                icon.classList.remove('in');
                element.classList.remove('collapsed');
            } else{
                icon.classList.add('in');
                element.classList.add('collapsed');
            }
        });
    });

})();
