(function () {
    var list = document.getElementById('rx-items');
    var addBtn = document.getElementById('add-rx-item');
    var template = document.getElementById('rx-item-template');
    if (!list || !template) {
        return;
    }

    function items() {
        return list.querySelectorAll('[data-rx-item]');
    }

    function nextIndex() {
        return items().length;
    }

    function retitle() {
        items().forEach(function (card, i) {
            var title = card.querySelector('.sneat-rx-item-title');
            if (title) {
                title.textContent = 'Medicine ' + (i + 1);
            }
        });
    }

    function bindRemove(button) {
        button.addEventListener('click', function () {
            var cards = items();
            if (cards.length <= 1) {
                var first = cards[0];
                first.querySelectorAll('input, select').forEach(function (input) {
                    input.value = '';
                });
                return;
            }
            button.closest('[data-rx-item]').remove();
            retitle();
        });
    }

    function bindCard(card) {
        var removeBtn = card.querySelector('.remove-rx-item');
        if (removeBtn) {
            bindRemove(removeBtn);
        }
    }

    items().forEach(bindCard);
    retitle();

    if (addBtn) {
        addBtn.addEventListener('click', function () {
            var html = template.innerHTML.replace(/__INDEX__/g, String(nextIndex()));
            var wrap = document.createElement('div');
            wrap.innerHTML = html.trim();
            var card = wrap.firstElementChild;
            list.appendChild(card);
            bindCard(card);
            retitle();
            var firstInput = card.querySelector('input[name*="[medicine_name]"]');
            if (firstInput) {
                firstInput.focus();
            }
            card.scrollIntoView({ behavior: 'smooth', block: 'center' });
        });
    }
})();
