document.addEventListener('alpine:init', () =>
{
  Alpine.data('topic', () => ({
    normalInputClass: "w-full rounded-lg px-3.5 py-3 outline-none focus:ring-2 focus:ring-offset-0 transition-all peer select-none border border-gray-400 focus:ring-blue-200 focus:ring-offset-white focus:border-blue-500 !text-base",
    filteredTopicList: JSON.parse(JSON.stringify(topicList)),
    filteredCardList: JSON.parse(JSON.stringify(cardList)),
    selectCard: '',
    selectedTopics: [],
    selectedCards: [],
    openCardDetailModal(id)
    {
      const card = this.filteredCardList.find(c => c.id == id);
      this.$dispatch('set-view-card-data', card);
      document.getElementById('cardDetailModal').setAttribute('open',true);
    },
    openEditCardModal(id)
    {
      const card = this.filteredCardList.find(c => c.id == id);
      this.$dispatch('set-edit-card-inputs', card);
      document.getElementById('editCardModal').setAttribute('open',true);
      this.$dispatch('close-view-card-modal');
    },
    filterTopicAndCard(search)
    {
      if (!search)
      {
        this.filteredTopicList = JSON.parse(JSON.stringify(topicList));
        this.filteredCardList = JSON.parse(JSON.stringify(cardList));
        return;
      }

      const normalizedSearch = this.removeDiacritics(search.toLowerCase());
      const searchKeywords = normalizedSearch.split(/[~`!@#$%^&*()_+\-\=\[\]{}\\|;':"<>,./? ]+/).filter(keyword => keyword);

      this.filteredTopicList = topicList.filter(topic => {
        const normalizedTopicName = this.removeDiacritics(topic.name.toLowerCase());
        return searchKeywords.some(keyword => normalizedTopicName.includes(keyword));
      });

      this.filteredCardList = cardList.filter(card => {
        const normalizedCardTitle = this.removeDiacritics(card.title.toLowerCase());
        return searchKeywords.some(keyword => normalizedCardTitle.includes(keyword));
      });
    },
    init()
    {
      this.$watch('selectedTopics', () => {
        this.$dispatch('update-select-topic', this.selectedTopics);
      });
      this.$watch('selectedCards', () => {
        this.$dispatch('update-select-card', this.selectedCards);
      });
    }
  }));

  Alpine.data('objectMove', () => ({
    selectedTopics: [],
    selectedCards: [],
    originalParent: objectMovingBreadcrumb.length > 0 ? objectMovingBreadcrumb[objectMovingBreadcrumb.length - 1].id : null,
    newParentTopic: objectMovingBreadcrumb.length > 0 ? objectMovingBreadcrumb[objectMovingBreadcrumb.length - 1].id : null,
    objectMovingBreadcrumb,
    parentTopicContent: [],
    filteredParentTopicContent: [],
    searchFilter: '',
    isLoading: false,
    async fetchParentTopicContent()
    {
      this.toggleLoading();

      const params = { parentTopicId: this.newParentTopic };
      const queryString = new URLSearchParams(params).toString();
      const url = `${ fetchTopicContentUrl }?${ queryString }`;

      await fetch(url)
        .then(response => response.json())
        .then(data =>
        {
          this.parentTopicContent = data.filter(elem => !this.selectedTopics.some(topic => topic == elem.id));
          this.applySearch();
          document.getElementById('objectMovingBreadcrumb').dispatchEvent(new CustomEvent('set-breadcrumb-items', { detail: this.objectMovingBreadcrumb }));
        })
        .catch(error =>
        {
          console.error("Error when fetching topic list: ", error);
          this.pushNotification(apiRequestError, 'error');
        })

      this.toggleLoading();
    },
    toggleLoading()
    {
      this.isLoading = !this.isLoading;

      if (this.isLoading)
      {
        document.getElementById('topic-content-list-loading').dispatchEvent(new CustomEvent('set-loading'));
      } else
      {
        document.getElementById('topic-content-list-loading').dispatchEvent(new CustomEvent('unset-loading'));
      }
    },
    applySearch()
    {
      if (!this.searchFilter)
      {
        this.filteredParentTopicContent = JSON.parse(JSON.stringify(this.parentTopicContent));
        return;
      }

      const normalizedSearch = this.removeDiacritics(this.searchFilter.toLowerCase());
      const searchKeywords = normalizedSearch.split(/[~`!@#$%^&*()_+\-\=\[\]{}\\|;':"<>,./? ]+/).filter(keyword => keyword);

      this.filteredParentTopicContent = this.parentTopicContent.filter(topic => {
        const normalizedTopicName = this.removeDiacritics(topic.name.toLowerCase());
        return searchKeywords.some(keyword => normalizedTopicName.includes(keyword));
      });
    },
    resetMoveObjectModal()
    {
      this.newParentTopic = this.originalParent;
    },
    init()
    {
      this.$watch('newParentTopic', (value) =>
      {
        const findIndex = this.objectMovingBreadcrumb.findIndex(elem => elem.id === value);

        if (findIndex !== -1)
        {
          this.objectMovingBreadcrumb = this.objectMovingBreadcrumb.slice(0, findIndex + 1);
        } else
        {
          const result = this.parentTopicContent.find(elem => elem.id === value);
          this.objectMovingBreadcrumb.push({
            id: result.id,
            label: result.name,
            action: `document.getElementById('moveObjectModal').dispatchEvent(new CustomEvent('set-new-parent-topic', { detail: ${result.id} }))`
          });
        }

        this.fetchParentTopicContent();
      });

      this.$watch('searchFilter', () =>
      {
        this.applySearch();
      });

      this.$watch('selectedTopics', () =>
      {
        this.fetchParentTopicContent();
      });

      this.fetchParentTopicContent();
    }
  }));
});
