document.addEventListener('alpine:init', () =>
{
  Alpine.data('cardBag', () => ({
    normalInputClass: "w-full rounded-lg px-3.5 py-3 outline-none focus:ring-2 focus:ring-offset-0 transition-all peer select-none border border-gray-400 focus:ring-blue-200 focus:ring-offset-white focus:border-blue-500 !text-base",
    filteredBagList: JSON.parse(JSON.stringify(bagList)),
    filteredCardList: JSON.parse(JSON.stringify(cardList)),
    selectCard: '',
    selectedBags: [],
    selectedCards: [],
    openCardDetailModal(id)
    {
      const card = this.filteredCardList.find(c => c.id === id);
      this.$dispatch('set-view-card-data', card);
      document.getElementById('card_detail_modal').setAttribute('open',true);
    },
    openEditCardModal(id)
    {

    },
    filterBagAndCard(search)
    {
      if (!search)
      {
        this.filteredBagList = JSON.parse(JSON.stringify(bagList));
        this.filteredCardList = JSON.parse(JSON.stringify(cardList));
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