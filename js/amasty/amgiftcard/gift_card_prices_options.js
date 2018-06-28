var AmastyGiftCardPriceOptions = {
    countElements: 0,
    init: function(countElements){
        if(!countElements) {
            countElements = 0
        }
        this.countElements = countElements;
    },
    addPriceRow: function(element_id, price, website_id){
        price = price ? price : '';
        this.countElements++;
        var template = new Template(
            '<tr id="' + element_id + '_' + this.countElements + '_tr">' +
                $(element_id + '_add_template')
                    .innerHTML
                    .replace(/disabled="no-template"/g, '')
                    .replace(/disabled/g, '')+
            '</tr>'
        );

        Element.insert($(element_id + '_container'), {bottom: template.evaluate({index:this.countElements, price:price})});

        if(website_id) {
            $(element_id + '_' + this.countElements + '_website').value = website_id;
        }
    },
    deletePriceRow: function(base_id){
        $(base_id + '_delete').value = 1;
        var tr = $(base_id + '_tr');
        Element.hide(tr)
        Element.addClassName(tr, 'template no-display');
        //this.countElements--;
    }
}