document.addEventListener('alpine:init', () =>
{
  Alpine.data('cardBag', () => ({
    normalInputClass: "w-full rounded-lg px-3.5 py-3 outline-none focus:ring-2 focus:ring-offset-0 transition-all peer select-none border border-gray-400 focus:ring-blue-200 focus:ring-offset-white focus:border-blue-500 !text-base",
    filteredBagList: JSON.parse(JSON.stringify(bagList)),
    filteredCardList: JSON.parse(JSON.stringify(cardList)),
    viewFilteredCardList: JSON.parse(JSON.stringify(viewCardList)),
    selectCard: '',
    selectedBags: [],
    selectedCards: [],
    resetNewBagModal()
    {
      document.getElementById('new_bag_name').dispatchEvent(new Event('reset'));
    },
    openCardDetailModal(index)
    {
      this.$dispatch('set-selected-card-data', this.viewFilteredCardList[index]);
      document.getElementById('card_detail_modal').setAttribute('open',true);
    },
    closeCardDetailModal()
    {
      this.$dispatch('set-selected-card-data', '');
      document.getElementById('card_detail_modal').removeAttribute('open');
    },
    openEditCardModal(index)
    {

    },
    filterBagAndCard(search)
    {
      if (!search)
      {
        this.filteredBagList = JSON.parse(JSON.stringify(bagList));
        this.filteredCardList = JSON.parse(JSON.stringify(cardList));
        this.viewFilteredCardList = JSON.parse(JSON.stringify(viewCardList));
        return;
      }

      const normalizedSearch = this.removeDiacritics(search.toLowerCase());
      const searchKeywords = normalizedSearch.split(/[~`!@#$%^&*()_+\-\=\[\]{}\\|;':"<>,./? ]+/).filter(keyword => keyword);

      this.filteredBagList = bagList.filter(bag => {
        const normalizedBagName = this.removeDiacritics(bag.name.toLowerCase());
        return searchKeywords.some(keyword => normalizedBagName.includes(keyword));
      });

      this.filteredCardList = cardList.filter(card => {
        const normalizedCardTitle = this.removeDiacritics(card.title.toLowerCase());
        return searchKeywords.some(keyword => normalizedCardTitle.includes(keyword));
      });

      this.viewFilteredCardList = viewCardList.filter(card => {
        const normalizedCardTitle = this.removeDiacritics(card.title.toLowerCase());
        return searchKeywords.some(keyword => normalizedCardTitle.includes(keyword));
      });
    },
    init()
    {
      this.$watch('selectedBags', () =>{
        this.$dispatch('update-select-bag', this.selectedBags);
      });
      this.$watch('selectedCards', () =>{
        this.$dispatch('update-select-card', this.selectedCards);
      });
    }
  }));
});