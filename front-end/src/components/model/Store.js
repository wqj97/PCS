/**
 * Created by wanqianjun on 2017/7/19.
 */
/* eslint-disable */
class Store {
  constructor (storeName, city) {
    this.storeName = storeName
    this.city = city
    this.product = []
  }

  addProduct (productName, productPrice) {
    this.product.push({
      proctName: productName,
      productPrice: productPrice
    })
  }
}

export default Store

/* eslint-enable */
