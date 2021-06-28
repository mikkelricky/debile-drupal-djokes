/* global fetch */
import './styles/app.scss'

import React, { useEffect, useState } from 'react'
import ReactDOM from 'react-dom'
import {
  Alert,
  Badge,
  Button,
  Card,
  Col,
  ProgressBar,
  Row
} from 'react-bootstrap'
import { useSwipeable } from 'react-swipeable'
import Settings from './Settings'
import useLocalStorage from './lib/useLocalStorage'

require('bootstrap')

const lunr = require('lunr')
require('lunr-languages/lunr.stemmer.support')(lunr)
require('lunr-languages/lunr.da')(lunr)

const el = document.getElementById('app')
const options = JSON.parse(el.dataset.options || '{}')

const Djokes = ({ djokes_data_url: dataUrl, total_number_of_items: totalNumberOfItems, collection = { title: 'Debile Djokes' } }) => {
  const [error, setError] = useState(null)
  const [isLoaded, setIsLoaded] = useState(false)
  const [items, setItems] = useState([])
  const [indexedItems, setIndexedItems] = useState([])
  const [index, setIndex] = useState(-1)
  const [searchQuery, setSearchQuery] = useState('')
  const [searchIndex, setSearchIndex] = useState(null)
  const [searchResult, setSearchResult] = useState([])
  const [showBody, setShowBody] = useState(true)

  const [showSettings, setShowSettings] = useState(false)
  const [showPunchline, setShowPunchline] = useState(false)
  const [alwaysShowPunchline, setAlwaysShowPunchline] = useLocalStorage('debile_djokes.alwaysShowPunchline', false)

  const fetchItems = (url, currentItems = []) => {
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

  const navigate = (offset) => {
    let newIndex = index + offset
    if (newIndex < 0) {
      newIndex = 0
    } else if (newIndex >= items.length) {
      newIndex = items.length - 1
    }
    setIndex(newIndex)
    setShowPunchline(false)
  }

  const prev = () => {
    navigate(-1)
  }

  const next = () => {
    navigate(1)
  }

  const SearchResult = ({ result }) => {
    const item = indexedItems[result.ref]

    const selectItem = (item) => {
      setSearchQuery('')
      setIndex(item.attributes.index - 1)
    }

    // <div key={item.ref}>{item.attributes.index} {JSON.stringify(indexedItems[item.ref])}</div>)
    return (
      <div onClick={() => selectItem(item)}>
        {item.attributes.index}: {item.attributes.djoke} {item.attributes.punchline}
      </div>
    )
  }

  const handlers = useSwipeable({
    onSwipedLeft: next,
    onSwipedRight: prev
  })

  if (error) {
    return <Alert variant='danger'>Error: {error.message}</Alert>
  } else if (!isLoaded) {
    return (
      <div>
        <ProgressBar animated now={100 * items.length / totalNumberOfItems} />
        Loading {collection.title} …
      </div>
    )
  } else {
    return (
      <div className='d-flex flex-column' {...handlers}>

        <div className='djoke-navigation'>
          <Row>
            <Col>
              <Button onClick={prev} disabled={index === 0}>Previous djoke</Button>
            </Col>
            {/* <Col className='text-center'> */}
            {/*   <Form.Control type='search' placeholder='Search' value={searchQuery} onChange={(event) => setSearchQuery(event.target.value)} /> */}
            {/* </Col> */}
            <Col className='text-end'>
              <Button onClick={next} disabled={items.length - 1 === index}>Next djoke</Button>
            </Col>
          </Row>
        </div>

        <div className='djoke-content flex-grow-1'>
          {items[index] &&
            <Card>
              <Card.Body>
                <Badge bg='primary' className='m-of-n'>
                  {index + 1}/{items.length}
                </Badge>
                <h5 className='card-title djoke'>{items[index].attributes.djoke}</h5>
                <p className='card-text text-end'>
                  {showPunchline || alwaysShowPunchline
                    ? <span className='punchline'>{items[index].attributes.punchline}</span>
                    : <span onClick={() => setShowPunchline(true)}>Show punchline</span>}
                </p>
              </Card.Body>
            </Card>}

          {searchResult && searchResult.length > 0 && searchResult.map(result => <SearchResult key={result.ref} result={result} />)}

          {showSettings && <Settings closeSettings={() => setShowSettings(false)} {...{ alwaysShowPunchline, setAlwaysShowPunchline }} />}
        </div>

        {false && showBody &&
          <Alert variant='info' dismissible onClose={() => setShowBody(false)}>
            <div dangerouslySetInnerHTML={{ __html: collection.body }} />
          </Alert>}

        <nav className='navbar fixed-bottom navbar-expand navbar-light bg-light justify-content-between'>
          <div className='container-fluid'>
            {/* <ul className='navbar-nav'>
              <li className="nav-item">
                <a className="nav-link" href="#">Search</a>
              </li>
            </ul> */}
            <ul className='navbar-nav'>
              <li className='nav-item'>
                <a className='nav-link' role='button' onClick={() => setShowSettings(true)}>Settings</a>
              </li>
            </ul>
          </div>
        </nav>

      </div>
    )
  }
}

const App = (options) => {
  return (
    <Djokes {...options} />
  )
}

ReactDOM.render(
  <App {...options} />,
  el
)
