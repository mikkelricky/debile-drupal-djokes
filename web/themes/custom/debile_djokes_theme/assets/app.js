/* global fetch */
import './styles/app.scss'

import React, { useEffect, useState } from 'react'
import ReactDOM from 'react-dom'
import { Badge, Button, Card, Col, Form, ProgressBar, Row } from 'react-bootstrap'

const lunr = require('lunr')
require('lunr-languages/lunr.stemmer.support')(lunr)
require('lunr-languages/lunr.da')(lunr)

const el = document.getElementById('app')
const options = JSON.parse(el.dataset.options || '{}')

const Djokes = ({ djokes_data_url: dataUrl, total_number_of_items: totalNumberOfItems }) => {
  const [error, setError] = useState(null)
  const [isLoaded, setIsLoaded] = useState(false)
  const [items, setItems] = useState([])
  const [indexedItems, setIndexedItems] = useState([])
  const [index, setIndex] = useState(-1)
  const [searchQuery, setSearchQuery] = useState('')
  const [searchIndex, setSearchIndex] = useState(null)
  const [searchResult, setSearchResult] = useState([])

  const fetchItems = (url, currentItems = []) => {
    console.log(url, currentItems.length)
    fetch(url)
      .then(res => res.json())
      .then(
        (result) => {
          currentItems = currentItems.concat(result.data)
          setItems(currentItems)
          const nextUrl = result.links?.next?.href
          if (nextUrl) {
            fetchItems(nextUrl, currentItems)
          } else {
            // Wait a little before showing djokes.
            setTimeout(() => {
              setIsLoaded(true)
              setIndex(0)
              setItems(currentItems)
            }, 1000)
          }
        },
        // Note: it's important to handle errors here
        // instead of a catch() block so that we don't swallow
        // exceptions from actual bugs in components.
        (error) => {
          setIsLoaded(true)
          setError(error)
        }
      )
  }

  // Note: the empty deps array [] means
  // this useEffect will run once
  // similar to componentDidMount()
  useEffect(() => {
    fetchItems(dataUrl)
  }, [])

  useEffect(() => {
    console.log('index', index)
  }, [index])

  useEffect(() => {
    console.log('search', searchQuery, searchIndex)
    if (searchIndex !== null) {
      setSearchResult(searchQuery ? searchIndex.search(searchQuery + '*') : [])
    }
  }, [searchQuery])

  useEffect(() => {
    // Build search index.
    if (items.length > 0) {
      setSearchIndex(lunr(function () {
        this.use(lunr.da)
        this.field('djoke')
        this.field('punchline')

        // Index items by id
        const indexed = {}
        items.forEach((item) => {
          indexed[item.id] = item
          this.add({ ...item.attributes, id: item.id })
        })
        setIndexedItems(indexed)
      }))
    }
  }, [items])

  const prev = () => {
    setIndex(index - 1 < 0 ? items.length - 1 : index - 1)
  }

  const next = () => {
    setIndex(index + 1 < items.length ? index + 1 : 0)
  }

  const SearchResult = ({ result }) => {
    const item = indexedItems[result.ref]

    // <div key={item.ref}>{item.attributes.index} {JSON.stringify(indexedItems[item.ref])}</div>)
    return (
      <div>
        {item.attributes.index}: {item.attributes.djoke} {item.attributes.punchline}
      </div>
    )
  }

  if (error) {
    return <div>Error: {error.message}</div>
  } else if (!isLoaded) {
    return (
      <div>
        <ProgressBar animated now={100 * items.length / totalNumberOfItems} />
        Loading djokes …
      </div>
    )
  } else {
    return (
      <>

        <Row className='djoke-navigation'>
          <Col>
            <Button onClick={prev} disabled={index === 0}>Previous djoke</Button>
          </Col>
          <Col className='text-center'>
            <Form.Control type='search' placeholder='Search' value={searchQuery} onChange={(event) => setSearchQuery(event.target.value)} />
          </Col>
          <Col className='text-right'>
            <Button onClick={next} disabled={items.length - 1 === index}>Next djoke</Button>
          </Col>
        </Row>

        {items[index] &&
          <Card>
            <Card.Body>
              <Badge bg='primary' className='m-of-n'>
                {index + 1}/{items.length}
              </Badge>
              <h5 className='card-title djoke'>{items[index].attributes.djoke}</h5>
              <p className='card-text text-end punchline'>{items[index].attributes.punchline}</p>
            </Card.Body>
          </Card>}

        {searchResult && searchResult.length > 0 && searchResult.map(result => <SearchResult key={result.ref} result={result} />)}
      </>
    )
  }
}

const App = (options) => {
  return (
    <div className='container-fluid'>
      <Djokes {...options} />
    </div>
  )
}

ReactDOM.render(
  <App {...options} />,
  el
)
