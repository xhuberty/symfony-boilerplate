<template>
  <b-container>
    <b-row align-h="center" class="mt-3 mb-3">
      <b-input
        id="inline-form-input-search"
        v-model="filters.search"
        type="text"
        :placeholder="$t('pages.root.search')"
        autofocus
        trim
        :debounce="debounce"
        @update="onSearch"
      ></b-input>
    </b-row>

    <b-overlay :show="isLoading" rounded="sm">
      <b-row align-h="center">
        <product-card-group :products="items" />
      </b-row>
      <b-row align-h="center">
        <b-pagination
          v-model="currentPage"
          :per-page="itemsPerPage"
          :total-rows="count"
          pills
          @change="onPaginate"
          @click.native="$scrollToTop"
        />
      </b-row>
    </b-overlay>
  </b-container>
</template>

<script>
import List, { calculateOffset, defaultItemsPerPage } from '@/mixins/list'
import ProductsQuery from '@/services/queries/products/products.query.gql'
import ProductCardGroup from '@/components/pages/products/ProductCardGroup'

export default {
  components: { ProductCardGroup },
  mixins: [List],
  async asyncData(context) {
    try {
      const result = await context.app.$graphql.request(ProductsQuery, {
        search: context.route.query.search || '',
        limit: defaultItemsPerPage,
        offset: calculateOffset(
          context.route.query.page || 1,
          defaultItemsPerPage
        ),
      })

      return {
        items: result.products.items,
        count: result.products.count,
      }
    } catch (e) {
      context.error(e)
    }
  },
  data() {
    return {
      filters: {
        search: this.$route.query.search || '',
      },
    }
  },
  methods: {
    async doSearch() {
      this.isLoading = true
      this.updateRouter()

      try {
        const result = await this.$graphql.request(ProductsQuery, {
          search: this.filters.search,
          limit: this.itemsPerPage,
          offset: this.offset,
        })

        this.items = result.products.items
        this.count = result.products.count
        this.isLoading = false
      } catch (e) {
        this.$nuxt.error(e)
      }
    },
  },
}
</script>
