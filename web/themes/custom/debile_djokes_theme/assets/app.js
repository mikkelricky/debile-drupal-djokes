/* global fetch */
import './styles/app.scss'

import React, { useEffect, useState } from 'react'
import ReactDOM from 'react-dom'
import { Alert, Badge, Carousel, ProgressBar } from 'react-bootstrap'
import Settings from './Settings'
import useLocalStorage from './lib/useLocalStorage'
import { useTranslation } from 'react-i18next'
import './i18n'

require('bootstrap')

const lunr = require('lunr')
require('lunr-languages/lunr.stemmer.support')(lunr)
require('lunr-languages/lunr.da')(lunr)

const el = document.getElementById('app')
const options = JSON.parse(el?.dataset.options || '{}')

const Djokes = ({ djokes_data_url: dataUrl, total_number_of_items: totalNumberOfItems, collection = { title: 'Debile Djokes' } }) => {
  const { t } = useTranslation()
  const [error, setError] = useState(null)
  const [isLoaded, setIsLoaded] = useState(false)
  const [loadedItems, setLoadedItems] = useState([])
  const [items, setItems] = useState(null)
  const [indexedItems, setIndexedItems] = useState([])
  const [index, setIndex] = useState(null)
  const [searchQuery, setSearchQuery] = useState('')
  const [searchIndex, setSearchIndex] = useState(null)
  const [searchResult, setSearchResult] = useState([])

  const [showSettings, setShowSettings] = useState(false)
  const [showPunchline, setShowPunchline] = useState(false)
  const [alwaysShowPunchline, setAlwaysShowPunchline] = useLocalStorage('debile_djokes.alwaysShowPunchline', false)

  const fetchItems = (url, currentItems = []) => {
    fetch(url)
      .then(res => res.json())
      .then(
        (result) => {
          currentItems = currentItems.concat(result.data)
          setLoadedItems(currentItems)
          const nextUrl = result.links?.next?.href
          if (nextUrl) {
            fetchItems(nextUrl, currentItems)
          } else {
            // Wait a little before showing djokes.
            setTimeout(() => {
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
    const url = new URL(window.location)
    const match = /^#(\d+)$/.exec(url.hash)
    if (match !== null) {
      setIndex(parseInt(match[1]) - 1)
    }

    fetchItems(dataUrl)
  }, [])

  useEffect(() => {
    if (index !== null) {
      const url = new URL(window.location)
      url.hash = index + 1
      window.history.replaceState({}, '', url)
    }
  }, [index])

  useEffect(() => {
    if (searchIndex !== null) {
      setSearchResult(searchQuery ? searchIndex.search(searchQuery + '*') : [])
    }
  }, [searchQuery])

  useEffect(() => {
    if (items !== null) {
      setIsLoaded(true)
      if (index === null || index < 0 || index > items.length - 1) {
        setIndex(0)
      }

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
    }
  }, [items])

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

  if (error) {
    return <Alert variant='danger'>{t('Error: {{message}}', { message: error.message })}</Alert>
  } else if (!isLoaded) {
    return (
      <div>
        <ProgressBar animated now={100 * (loadedItems?.length || 0) / totalNumberOfItems} />
        {t('Loading {{title}} …', { title: collection.title })}
      </div>
    )
  } else {
    return (
      <div className='d-flex flex-column'>

        <div className='djoke-content flex-grow-1'>
          {/* @see https://react-bootstrap.github.io/components/carousel/ */}
          <Carousel variant='dark' defaultActiveIndex={index} indicators={false} interval={null} onSlide={(index) => { setIndex(index); setShowPunchline(false) }}>
            {items && items.map((item, index) => (
              <Carousel.Item key={`djoke-${index}`}>
                <div className='djoke'>
                  <Badge bg='info' className='m-of-n'>{index + 1}</Badge>

                  <p className='text'>{item.attributes.djoke}</p>
                  <p className='text-end'>
                    {showPunchline || alwaysShowPunchline
                      ? <span className='punchline'>{item.attributes.punchline}</span>
                      : <span className='punchline hidden' onClick={() => setShowPunchline(true)}>{t('Show punchline')}</span>}
                  </p>

                </div>
              </Carousel.Item>
            ))}

          </Carousel>

          {searchResult && searchResult.length > 0 && searchResult.map(result => <SearchResult key={result.ref} result={result} />)}

          {showSettings && <Settings closeSettings={() => setShowSettings(false)} {...{ alwaysShowPunchline, setAlwaysShowPunchline }} />}
        </div>

        <nav className='navbar fixed-bottom navbar-expand navbar-light bg-light justify-content-between'>
          <div className='container-fluid'>
            {/* <ul className='navbar-nav'>
              <li className="nav-item">
                <a className="nav-link" href="#">Search</a>
              </li>
            </ul> */}
            <ul className='navbar-nav'>
              <li className='nav-item'>
                <a className='nav-link' role='button' onClick={() => setShowSettings(true)}>{t('Settings')}</a>
              </li>
            </ul>
          </div>
        </nav>

      </div>
    )
  }
}

// const App = (options) => {
//   return (
//     <Djokes {...options} />
//   )
// }

if (el !== null) {
  ReactDOM.render(
    <Djokes {...options} />,
    el
  )
}
